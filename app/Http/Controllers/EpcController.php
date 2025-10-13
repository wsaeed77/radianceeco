<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\EpcApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EpcController extends Controller
{
    protected $epcService;

    public function __construct(EpcApiService $epcService)
    {
        $this->middleware('auth');
        $this->epcService = $epcService;
    }

    /**
     * Fetch EPC certificate for a lead
     */
    public function fetchForLead(Request $request, Lead $lead)
    {
        try {
            // Get postcode and address from lead
            $postcode = $lead->zip_code ?? $lead->postcode;

            if (!$postcode) {
                return back()->with('error', 'Postcode is required to fetch EPC certificate');
            }

            // Fetch from API (only use postcode, not address, to get all properties)
            // User will select the correct property from the modal
            $result = $this->epcService->fetchCertificate($postcode, null);

            if ($result['success']) {
                Log::info('EPC Certificates Found', [
                    'lead_id' => $lead->id,
                    'count' => count($result['all_certificates'] ?? []),
                ]);
                
                // Check if multiple certificates found
                if (isset($result['all_certificates']) && count($result['all_certificates']) > 1) {
                    // Multiple certificates - return them for user selection
                    Log::info('Returning multiple certificates for selection', [
                        'count' => count($result['all_certificates']),
                    ]);
                    
                    return back()->with([
                        'epc_certificates' => $result['all_certificates'],
                        'info' => count($result['all_certificates']) . ' EPC certificates found. Please select the correct property.',
                    ]);
                }
                
                // Single certificate or auto-selected - format and store
                $formattedData = $this->epcService->formatEpcData($result['data']);
                
                // Update lead with EPC data
                $lead->update([
                    'epc_data' => $formattedData, // Don't json_encode - Laravel will do it automatically
                    'epc_rating' => $formattedData['current_energy_rating'],
                    'epc_details' => $formattedData['address'],
                    'epc_fetched_at' => now(),
                ]);

                Log::info('EPC Certificate Fetched', [
                    'lead_id' => $lead->id,
                    'certificate_number' => $formattedData['certificate_number'],
                ]);

                return back()->with('success', 'EPC certificate fetched successfully!');
            } else {
                Log::warning('EPC Fetch Failed', [
                    'lead_id' => $lead->id,
                    'postcode' => $postcode,
                    'message' => $result['message'],
                ]);

                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('EPC Fetch Exception', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to fetch EPC certificate: ' . $e->getMessage());
        }
    }

    /**
     * Save selected EPC certificate for a lead
     */
    public function saveSelectedCertificate(Request $request, Lead $lead)
    {
        $request->validate([
            'certificate_data' => 'required|json',
        ]);

        $certificateData = json_decode($request->certificate_data, true);
        
        // Format and store the EPC data
        $formattedData = $this->epcService->formatEpcData($certificateData);
        
        // Update lead with EPC data
        $update = [
            'epc_data' => $formattedData, // Don't json_encode - Laravel will do it automatically
            'epc_rating' => $formattedData['current_energy_rating'],
            'epc_details' => $formattedData['address'],
            'epc_fetched_at' => now(),
        ];

        // Optionally update address fields from selected certificate when requested
        if (($certificateData['__update_address'] ?? false) === true) {
            $addr1 = $certificateData['address'] ?? ($certificateData['address1'] ?? null);
            $addr2 = $certificateData['address2'] ?? null;
            $city = $certificateData['address3'] ?? null;
            $postcode = $certificateData['postcode'] ?? null;
            if ($addr1) $update['address_line_1'] = $addr1;
            if ($addr2) $update['address_line_2'] = $addr2;
            if ($city) $update['city'] = $city;
            if ($postcode) $update['zip_code'] = $postcode; // field named zip_code in forms
        }

        $lead->update($update);

        Log::info('EPC Certificate Selected and Saved', [
            'lead_id' => $lead->id,
            'certificate_number' => $formattedData['certificate_number'],
        ]);

        return back()->with('success', 'EPC certificate saved successfully!');
    }

    /**
     * Clear EPC data for a lead
     */
    public function clearForLead(Lead $lead)
    {
        $lead->update([
            'epc_data' => null,
            'epc_rating' => null,
            'epc_details' => null,
            'epc_fetched_at' => null,
                'epc_recommendations' => null,
                'epc_recommendations_fetched_at' => null,
        ]);

        return back()->with('success', 'EPC data cleared successfully!');
    }

        /**
         * Fetch and save EPC recommendations using the LMK key in saved epc_data
         */
        public function fetchRecommendations(Lead $lead, EpcApiService $service)
        {
            if (!$lead->epc_data || empty($lead->epc_data['certificate_number'])) {
                return back()->with('error', 'No EPC certificate found on this lead. Please fetch EPC first.');
            }

            $lmk = $lead->epc_data['certificate_number'];
            $result = $service->fetchRecommendationsByLmk($lmk);

            if (!$result['success']) {
                return back()->with('error', $result['message'] ?? 'Failed to fetch recommendations');
            }

            // Normalize recommendations to a flat array of associative items
            $items = [];
            $data = $result['data'];

            if (is_array($data)) {
                // Case 1: { recommendations: [...] }
                if (isset($data['recommendations']) && is_array($data['recommendations'])) {
                    $recs = $data['recommendations'];
                    // If first entry is a list of column names, convert matrix to assoc rows
                    if (isset($recs[0]) && is_array($recs[0])) {
                        $allStrings = true;
                        foreach ($recs[0] as $v) { if (!is_string($v)) { $allStrings = false; break; } }
                        if ($allStrings) {
                            $cols = $recs[0];
                            foreach (array_slice($recs, 1) as $row) {
                                if (is_array($row)) {
                                    $items[] = array_combine($cols, array_pad($row, count($cols), null));
                                }
                            }
                        } else {
                            $items = array_values($recs);
                        }
                    } else {
                        $items = array_values($recs);
                    }
                }

                // Case 2: { rows: [[...], [...]], column-names: [ ... ] }
                elseif (isset($data['rows']) && isset($data['column-names']) && is_array($data['rows']) && is_array($data['column-names'])) {
                    $cols = $data['column-names'];
                    foreach ($data['rows'] as $row) {
                        // If provider already returns associative rows, take as-is
                        $isAssoc = is_array($row) && array_keys($row) !== range(0, count((array)$row) - 1);
                        if ($isAssoc) {
                            $items[] = $row;
                        } else {
                            $items[] = array_combine($cols, array_pad((array)$row, count($cols), null));
                        }
                    }
                }

                // Case 3: Plain matrix where first element is a list of column names, rest are rows
                elseif (isset($data[0]) && is_array($data[0]) && count($data[0]) > 0 && array_values($data[0]) === $data[0]) {
                    $cols = $data[0];
                    foreach (array_slice($data, 1) as $row) {
                        if (is_array($row)) {
                            $items[] = array_combine($cols, array_pad($row, count($cols), null));
                        }
                    }
                }

                // Case 4: Already an array of associative items
                else {
                    $items = array_values($data);
                }
            }

            $lead->update([
                'epc_recommendations' => $items,
                'epc_recommendations_fetched_at' => now(),
            ]);

            Log::info('EPC recommendations saved', [
                'lead_id' => $lead->id,
                'count' => count($items),
                'sample' => $items[0] ?? null,
            ]);

            return back()->with('success', 'EPC recommendations fetched successfully.');
        }
}

