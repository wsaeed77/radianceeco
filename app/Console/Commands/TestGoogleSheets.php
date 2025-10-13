<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSheetsService;
use Google\Service\Drive;
use Exception;

class TestGoogleSheets extends Command
{
    protected $signature = 'test:google-sheets';
    protected $description = 'Test Google Sheets service account access';

    public function handle()
    {
        $this->info('🔍 Testing Google Sheets Access...');
        $this->newLine();

        try {
            $service = new GoogleSheetsService();
            $this->info('✓ Google Sheets Service initialized');
            
            $this->info('Attempting to list Google Sheets...');
            $this->newLine();
            
            // Try to list files
            $params = [
                'pageSize' => 10,
                'fields' => 'files(id, name, modifiedTime, owners, permissions)',
                'q' => "mimeType='application/vnd.google-apps.spreadsheet' and trashed=false",
                'orderBy' => 'modifiedTime desc',
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ];

            // Use the listSheets method
            $result = $service->listSheets(10);
            $files = $result['sheets'];
            
            if (empty($files)) {
                $this->warn('⚠️  No sheets found');
                $this->newLine();
                $this->info('Possible reasons:');
                $this->line('1. No sheets are shared with the service account');
                $this->line('2. Service account email: Check your credentials file');
                $this->newLine();
                
                // Try to show service account email
                $credPath = storage_path('app/google-drive-credentials.json');
                if (file_exists($credPath)) {
                    $creds = json_decode(file_get_contents($credPath), true);
                    $this->info('Service Account Email: ' . ($creds['client_email'] ?? 'Not found'));
                    $this->newLine();
                    $this->info('Share your Google Sheets with this email address!');
                }
            } else {
                $this->info('✓ Found ' . count($files) . ' sheet(s):');
                $this->newLine();
                
                foreach ($files as $file) {
                    $this->line('📄 ' . $file['name']);
                    $this->line('   ID: ' . $file['id']);
                    $this->line('   Modified: ' . $file['modified_time']);
                    $this->line('   Owner: ' . $file['owner']);
                    $this->newLine();
                }
            }
            
        } catch (Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->newLine();
            $this->line($e->getTraceAsString());
        }
    }
}
