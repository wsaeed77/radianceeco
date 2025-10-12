<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Enums\LeadStatus;
use App\Enums\LeadStage;
use App\Models\Activity;
use App\Models\Lead;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class LeadImportService
{
    /**
     * Import leads from a CSV file.
     *
     * @param string $filePath
     * @param int|null $userId
     * @return array
     */
    public function import(string $filePath, ?int $userId = null): array
    {
        $results = [
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'duplicates' => 0,
        ];
        
        $rows = Excel::toCollection(null, $filePath);
        
        if ($rows->isEmpty() || $rows[0]->isEmpty()) {
            return $results;
        }
        
        $headers = $this->normalizeHeaders($rows[0]->first());
        
        // Process all rows except the header
        $rows[0]->forget(0);
        
        foreach ($rows[0] as $index => $row) {
            try {
                $data = $this->mapRowToLeadData($row->toArray(), $headers);
                
                // Skip rows without a client name
                if (empty($data['client_name'])) {
                    $results['skipped']++;
                    continue;
                }
                
                // Check for duplicate by postcode and house number if available
                if (!empty($data['postcode']) && !empty($data['house_number'])) {
                    $dedupe_key = Lead::generateDedupeKey((object) $data);
                    $existingLead = Lead::where('dedupe_key', $dedupe_key)->first();
                    
                    if ($existingLead) {
                        // Update existing lead
                        $existingLead->update($data);
                        
                        // Record activity
                        Activity::create([
                            'lead_id' => $existingLead->id,
                            'type' => ActivityType::NOTE,
                            'message' => 'Lead updated via CSV import',
                            'created_by' => $userId,
                        ]);
                        
                        $results['updated']++;
                        $results['duplicates']++;
                        continue;
                    }
                }
                
                // Create new lead
                $lead = Lead::create($data);
                
                // Record activity
                Activity::create([
                    'lead_id' => $lead->id,
                    'type' => ActivityType::NOTE,
                    'message' => 'Lead created via CSV import',
                    'created_by' => $userId,
                ]);
                
                $results['imported']++;
                
            } catch (\Exception $e) {
                // Log error
                \Log::error('Failed to import lead at row ' . ($index + 2) . ': ' . $e->getMessage());
                $results['failed']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Normalize CSV headers to match our database fields.
     *
     * @param Collection $headers
     * @return array
     */
    protected function normalizeHeaders(Collection $headers): array
    {
        $normalizedHeaders = [];
        
        foreach ($headers as $index => $header) {
            $headerName = trim(strtolower($header));
            
            switch ($headerName) {
                case 'client name':
                    $normalizedHeaders[$index] = 'client_name';
                    break;
                case 'client dob':
                case 'date of birth':
                    $normalizedHeaders[$index] = 'client_dob';
                    break;
                case 'client number':
                case 'customer number':
                    $normalizedHeaders[$index] = 'client_number';
                    break;
                case 'phone':
                case 'phone number':
                case 'telephone':
                    $normalizedHeaders[$index] = 'phone';
                    break;
                case 'email':
                case 'email address':
                    $normalizedHeaders[$index] = 'email';
                    break;
                case 'house number':
                case 'house no':
                case 'property number':
                    $normalizedHeaders[$index] = 'house_number';
                    break;
                case 'street':
                case 'street name':
                case 'address line 1':
                    $normalizedHeaders[$index] = 'street_name';
                    break;
                case 'city':
                case 'town':
                    $normalizedHeaders[$index] = 'city';
                    break;
                case 'postcode':
                case 'post code':
                case 'zip':
                    $normalizedHeaders[$index] = 'postcode';
                    break;
                case 'address':
                case 'full address':
                case 'address line':
                    $normalizedHeaders[$index] = 'address_line';
                    break;
                case 'status':
                    $normalizedHeaders[$index] = 'status_raw';
                    break;
                case 'stage':
                case 'stage ':  // Note the space at the end
                    $normalizedHeaders[$index] = 'stage_raw';
                    break;
                case 'status.1':
                case 'status notes':
                case 'notes':
                    $normalizedHeaders[$index] = 'status_notes_raw';
                    break;
                case 'grant':
                    $normalizedHeaders[$index] = 'grant';
                    break;
                case 'job categories':
                case 'job category':
                    $normalizedHeaders[$index] = 'job_categories';
                    break;
                case 'possible grant':
                    $normalizedHeaders[$index] = 'possible_grant';
                    break;
                case 'benefit':
                case 'benefits':
                    $normalizedHeaders[$index] = 'benefit';
                    break;
                case 'poa':
                    $normalizedHeaders[$index] = 'poa';
                    break;
                case 'epc':
                    $normalizedHeaders[$index] = 'epc';
                    break;
                case 'gas safe':
                case 'gas certificate':
                    $normalizedHeaders[$index] = 'gas_safe';
                    break;
                case 'council tax band':
                case 'tax band':
                    $normalizedHeaders[$index] = 'council_tax_band';
                    break;
                case 'epr':
                case 'epr report':
                    $normalizedHeaders[$index] = 'epr_report';
                    break;
                case 'benefit holder':
                case 'benefit holder name':
                    $normalizedHeaders[$index] = 'benefit_holder_name';
                    break;
                case 'benefit holder dob':
                case 'benefit holder date of birth':
                    $normalizedHeaders[$index] = 'benefit_holder_dob';
                    break;
                case 'agent':
                case 'agent name':
                    $normalizedHeaders[$index] = 'agent';
                    break;
                default:
                    // Skip unknown headers
                    $normalizedHeaders[$index] = null;
            }
        }
        
        return $normalizedHeaders;
    }
    
    /**
     * Map a row of data to lead attributes.
     *
     * @param array $row
     * @param array $headers
     * @return array
     */
    protected function mapRowToLeadData(array $row, array $headers): array
    {
        $data = [];
        
        foreach ($headers as $index => $field) {
            if ($field && isset($row[$index])) {
                $value = trim($row[$index]);
                
                // Handle empty values
                if ($value === '') {
                    $value = null;
                }
                
                $data[$field] = $value;
            }
        }
        
        // Normalize status and stage
        if (isset($data['status_raw'])) {
            $data['status'] = LeadStatus::fromRaw($data['status_raw'])->value;
        }
        
        if (isset($data['stage_raw'])) {
            $data['stage'] = LeadStage::fromRaw($data['stage_raw'])->value;
        }
        
        // Format dates
        if (isset($data['client_dob']) && $data['client_dob']) {
            $data['client_dob'] = $this->parseDate($data['client_dob']);
        }
        
        if (isset($data['benefit_holder_dob']) && $data['benefit_holder_dob']) {
            $data['benefit_holder_dob'] = $this->parseDate($data['benefit_holder_dob']);
        }
        
        return $data;
    }
    
    /**
     * Parse various date formats to Y-m-d.
     *
     * @param string $date
     * @return string|null
     */
    protected function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        
        $parsedDate = null;
        
        // Try various date formats
        $formats = [
            'd/m/Y',
            'm/d/Y',
            'Y-m-d',
            'd-m-Y',
            'm-d-Y',
            'd.m.Y',
            'm.d.Y',
            'Y.m.d',
        ];
        
        foreach ($formats as $format) {
            $dateTime = \DateTime::createFromFormat($format, $date);
            if ($dateTime !== false) {
                $parsedDate = $dateTime->format('Y-m-d');
                break;
            }
        }
        
        // If still not parsed, try with strtotime
        if (!$parsedDate) {
            $timestamp = strtotime($date);
            if ($timestamp !== false) {
                $parsedDate = date('Y-m-d', $timestamp);
            }
        }
        
        return $parsedDate;
    }
}