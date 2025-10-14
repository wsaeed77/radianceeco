<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Services\GeocodingService;

class GeocodeLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:geocode {--force : Force re-geocode all leads}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Geocode leads addresses to get latitude/longitude coordinates';

    protected $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        parent::__construct();
        $this->geocodingService = $geocodingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        // Get leads that need geocoding
        $query = Lead::query();
        
        if (!$force) {
            $query->whereNull('latitude')
                  ->orWhereNull('longitude');
        }

        $leads = $query->get();
        $total = $leads->count();

        if ($total === 0) {
            $this->info('No leads need geocoding.');
            return 0;
        }

        $this->info("Geocoding {$total} leads...");
        $this->info("Note: Rate limited to 1 request/second (Nominatim policy)");
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($leads as $lead) {
            // Build address from lead data
            $address = $this->geocodingService->buildAddress([
                'address_line_1' => $lead->address_line_1,
                'address_line_2' => $lead->address_line_2,
                'city' => $lead->city,
                'zip_code' => $lead->zip_code,
            ]);

            // Try geocoding
            $coordinates = $this->geocodingService->geocode($address);

            if ($coordinates) {
                $lead->update([
                    'latitude' => $coordinates['latitude'],
                    'longitude' => $coordinates['longitude'],
                    'geocoded_at' => now(),
                ]);
                $success++;
            } else {
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Geocoding complete!");
        $this->info("✓ Success: {$success}");
        if ($failed > 0) {
            $this->warn("✗ Failed: {$failed}");
        }

        return 0;
    }
}
