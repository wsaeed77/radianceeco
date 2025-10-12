<?php

namespace App\Console\Commands;

use App\Services\EpcApiService;
use Illuminate\Console\Command;

class TestEpcApi extends Command
{
    protected $signature = 'test:epc {postcode=SW1A1AA}';
    protected $description = 'Test EPC API connection';

    public function handle(EpcApiService $epcService)
    {
        $postcode = $this->argument('postcode');
        
        $this->info("Testing EPC API with postcode: {$postcode}");
        $this->info("API URL: " . config('services.epc.url'));
        $this->info("API Key configured: " . (config('services.epc.key') ? 'Yes' : 'No'));
        
        if (config('services.epc.key')) {
            $keyLength = strlen(config('services.epc.key'));
            $hasColon = strpos(config('services.epc.key'), ':') !== false;
            $this->info("API Key length: {$keyLength}");
            $this->info("API Key has colon: " . ($hasColon ? 'Yes' : 'No'));
        }
        
        $this->newLine();
        $this->info("Fetching EPC certificate...");
        
        $result = $epcService->fetchCertificate($postcode);
        
        if ($result['success']) {
            $this->info("✅ Success!");
            $this->newLine();
            $this->info("Certificate Details:");
            $data = $result['data'];
            $this->table(
                ['Field', 'Value'],
                [
                    ['Address', $data['address'] ?? 'N/A'],
                    ['Postcode', $data['postcode'] ?? 'N/A'],
                    ['Current Rating', $data['current-energy-rating'] ?? 'N/A'],
                    ['Current Efficiency', $data['current-energy-efficiency'] ?? 'N/A'],
                    ['Property Type', $data['property-type'] ?? 'N/A'],
                    ['Lodgement Date', $data['lodgement-date'] ?? 'N/A'],
                ]
            );
        } else {
            $this->error("❌ Failed: " . $result['message']);
        }
        
        return 0;
    }
}

