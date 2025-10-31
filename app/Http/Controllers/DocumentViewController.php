<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Enums\DocumentKind;
use App\Models\Activity;
use App\Models\Document;
use App\Models\Lead;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for uploading a new document to a lead.
     */
    public function createForLead($leadId)
    {
        $lead = Lead::findOrFail($leadId);
        $documentKinds = DocumentKind::cases();
        
        return view('documents.create', compact('lead', 'documentKinds'));
    }

    /**
     * Show the form for uploading a new document to an activity.
     */
    public function createForActivity($leadId, $activityId)
    {
        $lead = Lead::findOrFail($leadId);
        $activity = Activity::findOrFail($activityId);
        $documentKinds = DocumentKind::cases();
        
        return view('documents.create', compact('lead', 'activity', 'documentKinds'));
    }

    /**
     * Store a newly uploaded document for a lead.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|string|exists:leads,id',
            'activity_id' => 'nullable|string|exists:activities,id',
            'kind' => 'required|string',
            'documents' => 'required|array|min:1',
            'documents.*' => 'required|file|max:10240', // 10MB max per file
        ]);
        
        $lead = Lead::findOrFail($validated['lead_id']);
        
        // Check if activity exists and belongs to the lead
        $activity = null;
        if (!empty($validated['activity_id'])) {
            $activity = Activity::where('id', $validated['activity_id'])
                                ->where('lead_id', $lead->id)
                                ->firstOrFail();
        }
        
        // Validate document kind
        try {
            $kind = DocumentKind::from($validated['kind']);
        } catch (\ValueError $e) {
            return back()->withErrors(['kind' => 'Invalid document type selected.']);
        }
        
        $files = $request->file('documents');
        $uploadedDocuments = [];
        $failedUploads = [];
        
        foreach ($files as $index => $file) {
            $originalName = $file->getClientOriginalName();
            $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            // Define the storage path
            $path = "documents/{$lead->id}/{$kind->value}/{$fileName}";
            
            // Store the file locally
            $stored = Storage::disk('public')->put($path, file_get_contents($file));
            
            if (!$stored) {
                $failedUploads[] = $originalName;
                continue;
            }
            
            $uploadedDocuments[] = [
                'file' => $file,
                'originalName' => $originalName,
                'fileName' => $fileName,
                'path' => $path,
            ];
        }
        
        if (empty($uploadedDocuments)) {
            return back()->withErrors(['documents' => 'Failed to upload any documents.']);
        }
        
        $createdDocuments = [];
        $googleDriveFolderId = null;
        
        // Get Google Drive folder ID once for all documents
        if (config('services.google_drive.enabled', false)) {
            try {
                $googleDriveService = new GoogleDriveService();
                
                if ($googleDriveService->isConfigured()) {
                    // Create/get lead folder
                    $leadFolderId = $googleDriveService->getOrCreateLeadFolder(
                        $lead->id,
                        "{$lead->first_name}_{$lead->last_name}"
                    );
                    
                    // Create/get document type folder
                    $googleDriveFolderId = $googleDriveService->getOrCreateDocumentTypeFolder(
                        $leadFolderId,
                        $kind->value
                    );
                }
            } catch (\Exception $e) {
                Log::error("Failed to setup Google Drive folders: " . $e->getMessage(), [
                    'lead_id' => $lead->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Process each uploaded file
        foreach ($uploadedDocuments as $uploadedDoc) {
            $file = $uploadedDoc['file'];
            $originalName = $uploadedDoc['originalName'];
            $fileName = $uploadedDoc['fileName'];
            $path = $uploadedDoc['path'];
            
            // Initialize document data
            $documentData = [
                'lead_id' => $lead->id,
                'activity_id' => $activity ? $activity->id : null,
                'kind' => $kind->value,
                'name' => $originalName,
                'disk' => 'public',
                'path' => $path,
                'size_bytes' => $file->getSize(),
                'uploaded_by' => Auth::id(),
                'uploaded_at' => now(),
            ];
            
            // Upload to Google Drive if enabled
            if ($googleDriveFolderId) {
                try {
                    $googleDriveService = new GoogleDriveService();
                    
                    // Upload file to Google Drive
                    $localFilePath = Storage::disk('public')->path($path);
                    $googleDriveFile = $googleDriveService->uploadFile(
                        $localFilePath,
                        $originalName,
                        $googleDriveFolderId,
                        $file->getMimeType()
                    );
                    
                    // Add Google Drive data
                    $documentData['google_drive_file_id'] = $googleDriveFile['id'];
                    $documentData['google_drive_folder_id'] = $googleDriveFolderId;
                    $documentData['google_drive_web_view_link'] = $googleDriveFile['webViewLink'];
                    $documentData['google_drive_web_content_link'] = $googleDriveFile['webContentLink'] ?? null;
                    
                    Log::info("Document uploaded to Google Drive", [
                        'lead_id' => $lead->id,
                        'document_name' => $originalName,
                        'google_drive_file_id' => $googleDriveFile['id']
                    ]);
                } catch (\Exception $e) {
                    // Log error but continue - local upload still succeeded
                    Log::error("Failed to upload to Google Drive: " . $e->getMessage(), [
                        'lead_id' => $lead->id,
                        'document_name' => $originalName,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Create document record
            $document = Document::create($documentData);
            $createdDocuments[] = $document;
        }
        
        // Create activity record if not already tied to an activity
        if (!$activity) {
            $documentNames = collect($createdDocuments)->pluck('name')->join(', ');
            $activity = Activity::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id(),
                'type' => ActivityType::FILE_UPLOAD,
                'description' => 'Uploaded documents: ' . $documentNames,
                'message' => 'Documents uploaded: ' . $documentNames . ' (' . $kind->name . ')',
                'created_by' => Auth::id(),
            ]);
        }
        
        // Prepare success message
        $successMessage = count($createdDocuments) . ' document' . (count($createdDocuments) > 1 ? 's' : '') . ' uploaded successfully.';
        if (!empty($failedUploads)) {
            $successMessage .= ' Failed to upload: ' . implode(', ', $failedUploads);
        }
        
        return redirect()->route('leads.show', $lead->id)
            ->with('success', $successMessage);
    }

    /**
     * Bulk delete multiple documents.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'document_ids' => 'required|array|min:1',
            'document_ids.*' => 'required|string|exists:documents,id',
        ]);

        $documentIds = $validated['document_ids'];
        $deletedCount = 0;
        $failedDeletions = [];

        foreach ($documentIds as $documentId) {
            try {
                $document = Document::findOrFail($documentId);
                
                // Check if user can access this document (if agent)
                if (Auth::user() && Auth::user()->isAgent() && $document->lead->agent_id !== Auth::id()) {
                    $failedDeletions[] = $document->name;
                    continue;
                }

                // Delete the file from storage
                if ($document->disk === 'public' && $document->path) {
                    Storage::disk('public')->delete($document->path);
                }

                // Delete the document record
                $document->delete();
                $deletedCount++;

            } catch (\Exception $e) {
                $failedDeletions[] = $document->name ?? "Document ID: {$documentId}";
                Log::error("Failed to delete document: " . $e->getMessage(), [
                    'document_id' => $documentId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Prepare success message
        $message = "{$deletedCount} document" . ($deletedCount !== 1 ? 's' : '') . " deleted successfully.";
        if (!empty($failedDeletions)) {
            $message .= " Failed to delete: " . implode(', ', $failedDeletions);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Download a document.
     */
    public function download($id)
    {
        $document = Document::findOrFail($id);
        
        if (Storage::disk($document->disk)->exists($document->path)) {
            return Storage::disk($document->disk)->download(
                $document->path,
                $document->name,
                ['Content-Type' => Storage::disk($document->disk)->mimeType($document->path)]
            );
        }
        
        return back()->withErrors(['error' => 'Document not found on storage.']);
    }

    /**
     * View a document in browser (inline display for images and PDFs).
     */
    public function view($id)
    {
        $document = Document::findOrFail($id);
        
        if (!Storage::disk($document->disk)->exists($document->path)) {
            return back()->withErrors(['error' => 'Document not found on storage.']);
        }
        
        // For local storage, use response()->file()
        if ($document->disk === 'public' || $document->disk === 'local') {
            $filePath = Storage::disk($document->disk)->path($document->path);
            $mimeType = Storage::disk($document->disk)->mimeType($document->path);
            
            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $document->name . '"',
            ]);
        }
        
        // For remote storage (S3, etc.), redirect to a temporary URL
        $url = Storage::disk($document->disk)->temporaryUrl(
            $document->path,
            now()->addMinutes(15)
        );
        
        return redirect($url);
    }

    /**
     * Delete a document.
     */
    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        $lead = $document->lead;
        
        // Delete file from local storage
        if (Storage::disk($document->disk)->exists($document->path)) {
            Storage::disk($document->disk)->delete($document->path);
        }
        
        // Delete from Google Drive if it exists
        if ($document->google_drive_file_id && config('services.google_drive.enabled', false)) {
            try {
                $googleDriveService = new GoogleDriveService();
                if ($googleDriveService->isConfigured()) {
                    $googleDriveService->deleteFile($document->google_drive_file_id);
                    Log::info("Document deleted from Google Drive", [
                        'lead_id' => $lead->id,
                        'document_id' => $document->id,
                        'google_drive_file_id' => $document->google_drive_file_id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to delete from Google Drive: " . $e->getMessage(), [
                    'document_id' => $document->id,
                    'google_drive_file_id' => $document->google_drive_file_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Delete document record
        $document->delete();
        
        return redirect()->route('leads.show', $lead->id)
            ->with('success', 'Document deleted successfully.');
    }
}