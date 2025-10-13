<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use App\Services\LeadImportService;
use App\Models\Lead;
use App\Models\User;
use App\Enums\LeadStatus;
use App\Enums\LeadStage;
use App\Enums\LeadSource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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
                            $value = $row[$columnIndex];
                            
                            // Skip empty values
                            if (empty($value)) {
                                continue;
                            }
                            
                            $leadData[$leadField] = $value;
                        }
                    }

                    // Transform data before saving
                    $leadData = $this->transformLeadData($leadData);

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
                        $leadData['status'] = $leadData['status'] ?? 'unknown';
                        $leadData['source'] = $leadData['source'] ?? 'Import';
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
            // Basic Information
            ['value' => 'first_name', 'label' => 'First Name', 'required' => false],
            ['value' => 'last_name', 'label' => 'Last Name', 'required' => false],
            ['value' => 'email', 'label' => 'Email', 'required' => false],
            ['value' => 'phone', 'label' => 'Phone', 'required' => false],
            
            // Address
            ['value' => 'address_line_1', 'label' => 'Address Line 1', 'required' => false],
            ['value' => 'address_line_2', 'label' => 'Address Line 2', 'required' => false],
            ['value' => 'street_name', 'label' => 'Street Name', 'required' => false],
            ['value' => 'house_number', 'label' => 'House Number', 'required' => false],
            ['value' => 'city', 'label' => 'City', 'required' => false],
            ['value' => 'zip_code', 'label' => 'Zip Code/Postcode', 'required' => false],
            ['value' => 'postcode', 'label' => 'Postcode', 'required' => false],
            
            // Lead Status & Source
            ['value' => 'status', 'label' => 'Status', 'required' => false],
            ['value' => 'stage', 'label' => 'Stage/Team', 'required' => false],
            ['value' => 'source', 'label' => 'Source', 'required' => false],
            ['value' => 'source_details', 'label' => 'Source Details', 'required' => false],
            ['value' => 'grant_type', 'label' => 'Grant Type', 'required' => false],
            ['value' => 'notes', 'label' => 'Notes', 'required' => false],
            
            // Data Match
            ['value' => 'benefit_holder_name', 'label' => 'Benefit Holder Name', 'required' => false],
            ['value' => 'benefit_holder_dob', 'label' => 'Benefit Holder DOB', 'required' => false],
            ['value' => 'data_match_status', 'label' => 'Data Match Status', 'required' => false],
            ['value' => 'data_match_remarks', 'label' => 'Data Match Remarks', 'required' => false],
            
            // Eligibility
            ['value' => 'occupancy_type', 'label' => 'Occupancy Type', 'required' => false],
            ['value' => 'eligibility_client_dob', 'label' => 'Client DOB', 'required' => false],
            ['value' => 'possible_grant_types', 'label' => 'Possible Grant', 'required' => false],
            ['value' => 'benefit_type', 'label' => 'Benefit', 'required' => false],
            ['value' => 'council_tax_band', 'label' => 'Council Tax Band', 'required' => false],
            ['value' => 'floor_area', 'label' => 'Floor Area', 'required' => false],
            ['value' => 'epc_rating', 'label' => 'EPC Rating', 'required' => false],
            ['value' => 'epc_details', 'label' => 'EPC Details', 'required' => false],
            ['value' => 'gas_safe_info', 'label' => 'GAS SAFE', 'required' => false],
            ['value' => 'poa_info', 'label' => 'Proof of Address (POA)', 'required' => false],
        ];
    }

    /**
     * Transform and normalize lead data from import
     */
    private function transformLeadData(array $data): array
    {
        // Transform status to enum value
        if (isset($data['status'])) {
            try {
                $status = LeadStatus::fromRaw($data['status']);
                $data['status'] = $status->value;
            } catch (\Exception $e) {
                Log::warning("Could not parse status '{$data['status']}', defaulting to unknown");
                $data['status'] = 'unknown';
            }
        }

        // Transform stage to enum value
        if (isset($data['stage'])) {
            try {
                $stage = LeadStage::fromRaw($data['stage']);
                $data['stage'] = $stage->value;
            } catch (\Exception $e) {
                Log::warning("Could not parse stage '{$data['stage']}', defaulting to unknown");
                $data['stage'] = 'unknown';
            }
        }

        // Transform source to enum value
        if (isset($data['source'])) {
            try {
                $source = LeadSource::fromRaw($data['source']);
                $data['source'] = $source->value;
            } catch (\Exception $e) {
                Log::warning("Could not parse source '{$data['source']}', defaulting to Unknown");
                $data['source'] = 'Unknown';
            }
        }

        // Sync postcode and zip_code (both columns exist in DB)
        if (isset($data['postcode']) && !isset($data['zip_code'])) {
            $data['zip_code'] = $data['postcode'];
        } elseif (isset($data['zip_code']) && !isset($data['postcode'])) {
            $data['postcode'] = $data['zip_code'];
        }

        // Transform dates (handle DD/MM/YYYY format common in UK)
        $dateFields = ['benefit_holder_dob', 'eligibility_client_dob'];
        foreach ($dateFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                try {
                    // Try to parse common date formats
                    $date = $this->parseDate($data[$field]);
                    if ($date) {
                        $data[$field] = $date->format('Y-m-d');
                    } else {
                        // Remove field if it can't be parsed (likely N/A, TBC, etc.)
                        unset($data[$field]);
                    }
                } catch (\Exception $e) {
                    Log::warning("Could not parse date '{$data[$field]}' for field {$field}: " . $e->getMessage());
                    unset($data[$field]);
                }
            }
        }

        return $data;
    }

    /**
     * Parse date in various formats
     */
    private function parseDate(string $dateString): ?Carbon
    {
        $dateString = trim($dateString);
        
        // Skip non-date values
        $nonDateValues = ['n/a', 'na', 'tbc', 'tbd', 'unknown', 'null', '-', ''];
        if (in_array(strtolower($dateString), $nonDateValues)) {
            return null;
        }
        
        // Try common UK/EU date formats first (DD/MM/YYYY)
        $formats = [
            'd/m/Y',     // 25/09/1967
            'd-m-Y',     // 25-09-1967
            'd.m.Y',     // 25.09.1967
            'Y-m-d',     // 1967-09-25
            'd/m/y',     // 25/09/67
            'd-m-y',     // 25-09-67
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateString);
                if ($date && $date->year > 1900 && $date->year < 2100) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try Carbon's parse as last resort
        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }
}
