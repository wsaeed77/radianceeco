<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use App\Services\LeadImportService;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    protected $googleSheetsService;
    protected $leadImportService;

    public function __construct(GoogleSheetsService $googleSheetsService, LeadImportService $leadImportService)
    {
        $this->googleSheetsService = $googleSheetsService;
        $this->leadImportService = $leadImportService;
    }

    /**
     * Show import page
     */
    public function index()
    {
        return Inertia::render('Import/Index');
    }

    /**
     * List all Google Sheets
     */
    public function listSheets(Request $request)
    {
        try {
            $pageToken = $request->input('page_token');
            $result = $this->googleSheetsService->listSheets(50, $pageToken);

            return response()->json([
                'success' => true,
                'sheets' => $result['sheets'],
                'next_page_token' => $result['next_page_token'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to list sheets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load Google Sheets: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sheet info and tabs
     */
    public function getSheetInfo(Request $request)
    {
        $request->validate([
            'spreadsheet_id' => 'required|string',
        ]);

        try {
            $info = $this->googleSheetsService->getSheetInfo($request->spreadsheet_id);

            return response()->json([
                'success' => true,
                'info' => $info,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get sheet info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load sheet information: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview sheet data
     */
    public function previewSheet(Request $request)
    {
        $request->validate([
            'spreadsheet_id' => 'required|string',
            'sheet_name' => 'required|string',
        ]);

        try {
            $preview = $this->googleSheetsService->previewSheet(
                $request->spreadsheet_id,
                $request->sheet_name,
                $request->input('limit', 10)
            );

            // Get available lead fields for mapping
            $leadFields = $this->getLeadFields();

            return response()->json([
                'success' => true,
                'preview' => $preview,
                'lead_fields' => $leadFields,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to preview sheet: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to preview sheet: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import leads from Google Sheets
     */
    public function importLeads(Request $request)
    {
        $request->validate([
            'spreadsheet_id' => 'required|string',
            'sheet_name' => 'required|string',
            'mapping' => 'required|array',
            'skip_duplicates' => 'boolean',
            'update_existing' => 'boolean',
        ]);

        try {
            $data = $this->googleSheetsService->getSheetData(
                $request->spreadsheet_id,
                $request->sheet_name
            );

            $mapping = $request->mapping;
            $skipDuplicates = $request->input('skip_duplicates', true);
            $updateExisting = $request->input('update_existing', false);

            $imported = 0;
            $skipped = 0;
            $updated = 0;
            $errors = [];

            foreach ($data['rows'] as $index => $row) {
                try {
                    // Map row data to lead fields
                    $leadData = [];
                    foreach ($mapping as $sheetColumn => $leadField) {
                        $columnIndex = array_search($sheetColumn, $data['headers']);
                        if ($columnIndex !== false && isset($row[$columnIndex])) {
                            $leadData[$leadField] = $row[$columnIndex];
                        }
                    }

                    // Check if lead exists (by email or phone)
                    $existingLead = null;
                    if (!empty($leadData['email'])) {
                        $existingLead = Lead::where('email', $leadData['email'])->first();
                    } elseif (!empty($leadData['phone'])) {
                        $existingLead = Lead::where('phone', $leadData['phone'])->first();
                    }

                    if ($existingLead) {
                        if ($updateExisting) {
                            $existingLead->update($leadData);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        // Set default values
                        $leadData['status'] = $leadData['status'] ?? 'new';
                        $leadData['source'] = $leadData['source'] ?? 'import';
                        $leadData['assigned_to'] = $leadData['assigned_to'] ?? auth()->id();

                        Lead::create($leadData);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    Log::error("Import error on row " . ($index + 2) . ": " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errors,
                'total' => count($data['rows']),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to import leads: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to import leads: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available lead fields for mapping
     */
    private function getLeadFields()
    {
        return [
            ['value' => 'first_name', 'label' => 'First Name', 'required' => true],
            ['value' => 'last_name', 'label' => 'Last Name', 'required' => true],
            ['value' => 'email', 'label' => 'Email', 'required' => false],
            ['value' => 'phone', 'label' => 'Phone', 'required' => true],
            ['value' => 'street_name', 'label' => 'Street Name', 'required' => false],
            ['value' => 'house_number', 'label' => 'House Number', 'required' => false],
            ['value' => 'city', 'label' => 'City', 'required' => false],
            ['value' => 'zip_code', 'label' => 'Zip Code', 'required' => false],
            ['value' => 'status', 'label' => 'Status', 'required' => false],
            ['value' => 'source', 'label' => 'Source', 'required' => false],
            ['value' => 'notes', 'label' => 'Notes', 'required' => false],
        ];
    }
}
