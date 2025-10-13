<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EpcApiService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        // UK Government EPC API
        $this->apiUrl = config('services.epc.url', 'https://epc.opendatacommunities.org/api/v1');
        $this->apiKey = config('services.epc.key');
    }

    /**
     * Fetch recommendations by LMK key
     * Docs: https://epc.opendatacommunities.org/openapi/index.html#/domestic/get_domestic_recommendations__lmk_key_
     */
    public function fetchRecommendationsByLmk(string $lmkKey)
    {
        try {
            if (!$this->apiKey) {
                return [
                    'success' => false,
                    'message' => 'EPC API key is not configured. Please add EPC_API_KEY to your .env file.',
                ];
            }

            $url = rtrim($this->apiUrl, '/') . "/domestic/recommendations/{$lmkKey}";

            if (strpos($this->apiKey, ':') !== false) {
                list($username, $password) = explode(':', $this->apiKey, 2);
            } else {
                $username = $this->apiKey;
                $password = '';
            }

            $response = Http::withBasicAuth($username, $password)
                ->accept('application/json')
                ->get($url);

            if (!$response->successful()) {
                Log::error('EPC Recommendations API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'message' => 'Failed to fetch recommendations',
                    'status' => $response->status(),
                ];
            }

            $data = $response->json();
            // API returns an array of recommendation items
            return [
                'success' => true,
                'data' => $data,
            ];

        } catch (\Exception $e) {
            Log::error('EPC Recommendations Exception', [
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Error connecting to EPC API: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch EPC certificate by postcode and address
     */
    public function fetchCertificate($postcode, $address = null)
    {
        try {
            if (!$this->apiKey) {
                return [
                    'success' => false,
                    'message' => 'EPC API key is not configured. Please add EPC_API_KEY to your .env file.',
                ];
            }

            $url = "{$this->apiUrl}/domestic/search";
            
            $params = [
                'postcode' => $this->formatPostcode($postcode),
                'size' => 100, // Request up to 100 results per page
            ];

            // Only add address if it's not empty/whitespace
            if ($address && trim($address) !== '') {
                $params['address'] = trim($address);
            }

            // Parse username and password from API key
            // Format: username:password or just api_key
            if (strpos($this->apiKey, ':') !== false) {
                list($username, $password) = explode(':', $this->apiKey, 2);
            } else {
                $username = $this->apiKey;
                $password = '';
            }
            
            Log::info('EPC API Request Parameters', [
                'postcode' => $params['postcode'],
                'address' => $params['address'] ?? 'not provided',
                'size' => $params['size'],
            ]);
            
            // Make API request with Basic Auth and handle pagination
            $allRows = [];
            $currentUrl = $url;
            $pageCount = 0;
            
            do {
                $response = Http::withBasicAuth($username, $password)
                    ->accept('application/json')
                    ->get($currentUrl, $pageCount === 0 ? $params : []);

                if (!$response->successful()) {
                    Log::error('EPC API Error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    break;
                }

                $data = $response->json();
                $pageCount++;
                
                if (isset($data['rows']) && count($data['rows']) > 0) {
                    $allRows = array_merge($allRows, $data['rows']);
                }
                
                Log::info('EPC API Page Response', [
                    'page' => $pageCount,
                    'rows_in_page' => count($data['rows'] ?? []),
                    'total_so_far' => count($allRows),
                    'has_next' => isset($data['next']),
                ]);
                
                // Check if there's a next page
                $currentUrl = $data['next'] ?? null;
                
            } while ($currentUrl && $pageCount < 10); // Limit to 10 pages max for safety
            
            if (count($allRows) > 0) {
                // Return the most recent certificate
                $certificates = collect($allRows)->sortByDesc('lodgement-date');
                
                Log::info('EPC Certificates Processed', [
                    'total_found' => $certificates->count(),
                    'total_pages' => $pageCount,
                ]);
                
                return [
                    'success' => true,
                    'data' => $certificates->first(),
                    'all_certificates' => $certificates->values()->all(), // Return all certificates
                ];
            }

            return [
                'success' => false,
                'message' => 'No EPC certificate found for this address',
            ];
        } catch (\Exception $e) {
            Log::error('EPC API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Error connecting to EPC API: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Parse and format EPC data for storage
     */
    public function formatEpcData($epcData)
    {
        if (!$epcData) {
            return null;
        }

        return [
            // Basic Information
            'certificate_number' => $epcData['lmk-key'] ?? null,
            'lodgement_date' => $epcData['lodgement-date'] ?? null,
            'address' => $epcData['address'] ?? null,
            'postcode' => $epcData['postcode'] ?? null,
            
            // Energy Ratings
            'current_energy_rating' => $epcData['current-energy-rating'] ?? null,
            'current_energy_efficiency' => $epcData['current-energy-efficiency'] ?? null,
            'potential_energy_rating' => $epcData['potential-energy-rating'] ?? null,
            'potential_energy_efficiency' => $epcData['potential-energy-efficiency'] ?? null,
            
            // Environmental Impact
            'co2_emissions_current' => $epcData['co2-emissions-current'] ?? null,
            'co2_emissions_potential' => $epcData['co2-emissions-potential'] ?? null,
            'co2_emiss_curr_per_floor_area' => $epcData['co2-emiss-curr-per-floor-area'] ?? null,
            
            // Costs
            'lighting_cost_current' => $epcData['lighting-cost-current'] ?? null,
            'heating_cost_current' => $epcData['heating-cost-current'] ?? null,
            'hot_water_cost_current' => $epcData['hot-water-cost-current'] ?? null,
            'lighting_cost_potential' => $epcData['lighting-cost-potential'] ?? null,
            'heating_cost_potential' => $epcData['heating-cost-potential'] ?? null,
            'hot_water_cost_potential' => $epcData['hot-water-cost-potential'] ?? null,
            
            // Property Details
            'property_type' => $epcData['property-type'] ?? null,
            'built_form' => $epcData['built-form'] ?? null,
            'total_floor_area' => $epcData['total-floor-area'] ?? null,
            'number_habitable_rooms' => $epcData['number-habitable-rooms'] ?? null,
            'number_heated_rooms' => $epcData['number-heated-rooms'] ?? null,
            'construction_age_band' => $epcData['construction-age-band'] ?? null,
            
            // Features
            'walls_description' => $epcData['walls-description'] ?? null,
            'walls_energy_eff' => $epcData['walls-energy-eff'] ?? null,
            'roof_description' => $epcData['roof-description'] ?? null,
            'roof_energy_eff' => $epcData['roof-energy-eff'] ?? null,
            'windows_description' => $epcData['windows-description'] ?? null,
            'windows_energy_eff' => $epcData['windows-energy-eff'] ?? null,
            'main_heating_description' => $epcData['mainheat-description'] ?? null,
            'main_heating_energy_eff' => $epcData['mainheat-energy-eff'] ?? null,
            'main_heating_controls' => $epcData['mainheatc-description'] ?? null,
            'main_heating_controls_energy_eff' => $epcData['mainheatc-energy-eff'] ?? null,
            'hot_water_description' => $epcData['hotwater-description'] ?? null,
            'hot_water_energy_eff' => $epcData['hotwater-energy-eff'] ?? null,
            'lighting_description' => $epcData['lighting-description'] ?? null,
            'lighting_energy_eff' => $epcData['lighting-energy-eff'] ?? null,
            'floor_description' => $epcData['floor-description'] ?? null,
            'floor_energy_eff' => $epcData['floor-energy-eff'] ?? null,
            'secondheat_description' => $epcData['secondheat-description'] ?? null,
            
            // Recommendations
            'improvements' => $this->extractImprovements($epcData),
            
            // Inspection Details
            'inspection_date' => $epcData['inspection-date'] ?? null,
            'local_authority_label' => $epcData['local-authority-label'] ?? null,
            'constituency_label' => $epcData['constituency-label'] ?? null,
            
            // Additional
            'tenure' => $epcData['tenure'] ?? null,
            'transaction_type' => $epcData['transaction-type'] ?? null,
            'environment_impact_current' => $epcData['environment-impact-current'] ?? null,
            'environment_impact_potential' => $epcData['environment-impact-potential'] ?? null,
        ];
    }

    /**
     * Extract improvement recommendations from EPC data
     */
    private function extractImprovements($epcData)
    {
        $improvements = [];
        
        for ($i = 1; $i <= 10; $i++) {
            if (isset($epcData["improvement-item-{$i}"])) {
                $improvements[] = [
                    'description' => $epcData["improvement-descr-text-{$i}"] ?? '',
                    'indicative_cost' => $epcData["improvement-id-text-{$i}"] ?? '',
                ];
            }
        }
        
        return !empty($improvements) ? json_encode($improvements) : null;
    }

    /**
     * Format postcode for API
     */
    private function formatPostcode($postcode)
    {
        // Remove spaces and convert to uppercase
        return strtoupper(str_replace(' ', '', $postcode));
    }

    /**
     * Get energy efficiency rating label (A-G)
     */
    public function getEnergyRatingLabel($score)
    {
        if ($score >= 92) return 'A';
        if ($score >= 81) return 'B';
        if ($score >= 69) return 'C';
        if ($score >= 55) return 'D';
        if ($score >= 39) return 'E';
        if ($score >= 21) return 'F';
        return 'G';
    }

    /**
     * Get energy efficiency description
     */
    public function getEnergyEfficiencyDescription($rating)
    {
        $descriptions = [
            'Very Good' => ['very good', 'verygood'],
            'Good' => ['good'],
            'Average' => ['average'],
            'Poor' => ['poor'],
            'Very Poor' => ['very poor', 'verypoor'],
        ];

        $ratingLower = strtolower($rating ?? '');
        
        foreach ($descriptions as $label => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($ratingLower, $keyword) !== false) {
                    return $label;
                }
            }
        }
        
        return 'N/A';
    }
}

