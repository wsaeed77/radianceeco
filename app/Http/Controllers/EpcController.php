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
        $lead->update([
            'epc_data' => $formattedData, // Don't json_encode - Laravel will do it automatically
            'epc_rating' => $formattedData['current_energy_rating'],
            'epc_details' => $formattedData['address'],
            'epc_fetched_at' => now(),
        ]);

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
        ]);

        return back()->with('success', 'EPC data cleared successfully!');
    }
}

