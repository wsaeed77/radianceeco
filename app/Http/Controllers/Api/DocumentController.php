<?php

namespace App\Http\Controllers\Api;

use App\Enums\ActivityType;
use App\Enums\DocumentKind;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Document;
use App\Models\Lead;
use Aws\S3\S3Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentController extends Controller
{
    /**
     * Get all documents for a lead.
     */
    public function index(string $leadId): JsonResponse
    {
        $lead = Lead::findOrFail($leadId);
        
        // Check if user can access this lead (if agent)
        if (Auth::user() && Auth::user()->isAgent() && $lead->agent_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $documents = $lead->documents()
            ->with('uploader')
            ->orderBy('uploaded_at', 'desc')
            ->get();
            
        return response()->json($documents);
    }

    /**
     * Get a pre-signed URL for uploading a document to S3.
     */
    public function getPresignedUrl(Request $request, string $leadId): JsonResponse
    {
        $lead = Lead::findOrFail($leadId);
        
        // Check if user can access this lead (if agent)
        if (Auth::user() && Auth::user()->isAgent() && $lead->agent_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'filename' => 'required|string|max:255',
            'kind' => 'required|string',
            'content_type' => 'required|string|max:100',
        ]);
        
        // Validate document kind
        try {
            $kind = DocumentKind::from($request->kind);
        } catch (\ValueError $e) {
            throw ValidationException::withMessages([
                'kind' => ['Invalid document kind.'],
            ]);
        }
        
        // Create S3 client
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.s3.region'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
        
        // Generate a unique file path
        $extension = pathinfo($request->filename, PATHINFO_EXTENSION);
        $filename = Str::slug(pathinfo($request->filename, PATHINFO_FILENAME));
        $path = "documents/{$leadId}/{$kind->value}/" . $filename . '-' . Str::random(8) . '.' . $extension;
        
        // Generate presigned URL
        $command = $s3Client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $path,
            'ContentType' => $request->content_type,
            'ACL' => 'private',
        ]);
        
        $presignedRequest = $s3Client->createPresignedRequest($command, '+15 minutes');
        $presignedUrl = (string) $presignedRequest->getUri();
        
        return response()->json([
            'url' => $presignedUrl,
            'path' => $path,
            'expires' => now()->addMinutes(15)->toIso8601String(),
        ]);
    }

    /**
     * Store metadata for an uploaded document.
     */
    public function store(Request $request, string $leadId): JsonResponse
    {
        $lead = Lead::findOrFail($leadId);
        
        // Check if user can access this lead (if agent)
        if (Auth::user() && Auth::user()->isAgent() && $lead->agent_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'kind' => 'required|string',
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:500',
            'size_bytes' => 'nullable|integer',
        ]);
        
        // Validate document kind
        try {
            $kind = DocumentKind::from($request->kind);
        } catch (\ValueError $e) {
            throw ValidationException::withMessages([
                'kind' => ['Invalid document kind.'],
            ]);
        }
        
        // Create document record
        $document = Document::create([
            'lead_id' => $lead->id,
            'kind' => $kind->value,
            'name' => $request->name,
            'disk' => 's3',
            'path' => $request->path,
            'size_bytes' => $request->size_bytes,
            'uploaded_by' => Auth::id(),
            'uploaded_at' => now(),
        ]);
        
        // Create activity record
        Activity::create([
            'lead_id' => $lead->id,
            'type' => ActivityType::FILE_UPLOAD,
            'message' => 'Uploaded document: ' . $request->name . ' (' . $kind->value . ')',
            'created_by' => Auth::id(),
        ]);
        
        return response()->json($document, 201);
    }

    /**
     * Get a pre-signed URL for downloading a document from S3.
     */
    public function getDownloadUrl(string $id): JsonResponse
    {
        $document = Document::findOrFail($id);
        $lead = $document->lead;
        
        // Check if user can access this lead (if agent)
        if (Auth::user() && Auth::user()->isAgent() && $lead->agent_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Create S3 client
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.s3.region'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
        
        // Generate presigned URL for downloading
        $command = $s3Client->getCommand('GetObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $document->path,
            'ResponseContentDisposition' => 'attachment; filename="' . $document->name . '"',
        ]);
        
        $presignedRequest = $s3Client->createPresignedRequest($command, '+15 minutes');
        $presignedUrl = (string) $presignedRequest->getUri();
        
        return response()->json([
            'url' => $presignedUrl,
            'expires' => now()->addMinutes(15)->toIso8601String(),
        ]);
    }

    /**
     * Remove the specified document.
     */
    public function destroy(string $id): JsonResponse
    {
        $document = Document::findOrFail($id);
        $lead = $document->lead;
        
        // Only admins and managers can delete documents
        if (Auth::user() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Create S3 client
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.s3.region'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
        
        // Delete from S3
        $s3Client->deleteObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $document->path,
        ]);
        
        // Create activity record
        Activity::create([
            'lead_id' => $lead->id,
            'type' => ActivityType::NOTE,
            'message' => 'Deleted document: ' . $document->name . ' (' . $document->kind->value . ')',
            'created_by' => Auth::id(),
        ]);
        
        // Delete document record
        $document->delete();
        
        return response()->json(null, 204);
    }
}
