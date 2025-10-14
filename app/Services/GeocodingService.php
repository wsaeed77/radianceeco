<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeocodingService
{
    /**
     * Geocode an address using Nominatim (OpenStreetMap)
     * 
     * @param string $address Full address or postcode
     * @return array|null ['latitude' => float, 'longitude' => float] or null if failed
     */
    public function geocode(string $address): ?array
    {
        if (empty($address)) {
            return null;
        }

        // Create cache key from address
        $cacheKey = 'geocode_' . md5($address);

        // Check cache first (cache for 30 days)
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Nominatim API (OpenStreetMap)
            // Rate limit: 1 request per second
            sleep(1); // Respect rate limit

            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => config('app.name') . ' Lead Management System',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'gb', // UK only
                    'addressdetails' => 1,
                ]);

            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0];
                
                $result = [
                    'latitude' => (float) $data['lat'],
                    'longitude' => (float) $data['lon'],
                ];

                // Cache the result
                Cache::put($cacheKey, $result, now()->addDays(30));

                Log::info("Geocoded address: {$address}", $result);

                return $result;
            }

            Log::warning("Geocoding failed for address: {$address}");
            return null;

        } catch (\Exception $e) {
            Log::error("Geocoding error for address: {$address}", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Geocode using UK postcode (more accurate)
     * 
     * @param string $postcode UK postcode
     * @return array|null
     */
    public function geocodePostcode(string $postcode): ?array
    {
        return $this->geocode($postcode . ', UK');
    }

    /**
     * Build full address from lead data
     * 
     * @param array $leadData
     * @return string
     */
    public function buildAddress(array $leadData): string
    {
        $parts = array_filter([
            $leadData['address_line_1'] ?? '',
            $leadData['address_line_2'] ?? '',
            $leadData['city'] ?? '',
            $leadData['zip_code'] ?? '',
            'UK'
        ]);

        return implode(', ', $parts);
    }
}

