<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleDriveService;
use Exception;

class TestGoogleDrive extends Command
{
    protected $signature = 'test:google-drive {--upload-test : Try uploading a test file}';
    protected $description = 'Test Google Drive connection and configuration';

    public function handle()
    {
        $this->info('🔍 Testing Google Drive Setup...');
        $this->newLine();

        // Step 1: Check if credentials file exists
        $credentialsPath = storage_path('app/google-drive-credentials.json');
        $this->info('Step 1: Checking credentials file...');
        
        if (!file_exists($credentialsPath)) {
            $this->error("❌ Credentials file NOT found at: {$credentialsPath}");
            $this->info("   Please ensure google-drive-credentials.json is in storage/app/");
            return 1;
        }
        
        $this->info("✓ Credentials file found at: {$credentialsPath}");
        
        // Check if file is readable
        if (!is_readable($credentialsPath)) {
            $this->error("❌ Credentials file exists but is not readable");
            $this->info("   Check file permissions");
            return 1;
        }
        
        $this->info("✓ Credentials file is readable");
        
        // Validate JSON
        $json = file_get_contents($credentialsPath);
        $credentials = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("❌ Credentials file is not valid JSON");
            $this->info("   Error: " . json_last_error_msg());
            return 1;
        }
        
        $this->info("✓ Credentials file is valid JSON");
        
        // Check required fields
        $requiredFields = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email'];
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($credentials[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->error("❌ Missing required fields in credentials: " . implode(', ', $missing));
            return 1;
        }
        
        $this->info("✓ All required credential fields present");
        $this->info("   Service Account Email: " . $credentials['client_email']);
        $this->newLine();

        // Step 2: Check .env configuration
        $this->info('Step 2: Checking .env configuration...');
        
        $enabled = config('services.google_drive.enabled');
        if (!$enabled) {
            $this->warn("⚠️  GOOGLE_DRIVE_ENABLED is set to false or missing in .env");
            $this->info("   Add to .env: GOOGLE_DRIVE_ENABLED=true");
            $this->info("   Continuing test anyway...");
        } else {
            $this->info("✓ GOOGLE_DRIVE_ENABLED=true");
        }
        
        $rootFolderId = config('services.google_drive.root_folder_id');
        if ($rootFolderId) {
            $this->info("✓ GOOGLE_DRIVE_ROOT_FOLDER_ID={$rootFolderId}");
        } else {
            $this->warn("⚠️  GOOGLE_DRIVE_ROOT_FOLDER_ID not set (files will be in root)");
        }
        $this->newLine();

        // Step 3: Test Google Drive Service initialization
        $this->info('Step 3: Testing Google Drive Service...');
        
        try {
            $service = new GoogleDriveService();
            $this->info("✓ GoogleDriveService initialized successfully");
        } catch (Exception $e) {
            $this->error("❌ Failed to initialize GoogleDriveService");
            $this->error("   Error: " . $e->getMessage());
            $this->newLine();
            $this->info("Stack trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }
        $this->newLine();

        // Step 4: Test creating a folder
        $this->info('Step 4: Testing folder creation...');
        
        try {
            $testFolderId = $service->getOrCreateLeadFolder('test-123', 'Test Lead');
            $this->info("✓ Successfully created/found test folder");
            $this->info("   Folder ID: {$testFolderId}");
        } catch (Exception $e) {
            $this->error("❌ Failed to create folder");
            $this->error("   Error: " . $e->getMessage());
            $this->newLine();
            $this->info("Common causes:");
            $this->line("   • Service account doesn't have permission to create folders");
            $this->line("   • Root folder ID is invalid or not shared with service account");
            $this->line("   • Google Drive API is not enabled in Google Cloud Console");
            return 1;
        }
        $this->newLine();

        // Step 5: Optional file upload test
        if ($this->option('upload-test')) {
            $this->info('Step 5: Testing file upload...');
            
            // Create a test file
            $testFilePath = storage_path('app/test-google-drive.txt');
            file_put_contents($testFilePath, "This is a test file created at " . now()->toDateTimeString());
            
            try {
                $uploadResult = $service->uploadFile(
                    $testFilePath,
                    'test-upload.txt',
                    $testFolderId,
                    'text/plain'
                );
                
                $this->info("✓ Successfully uploaded test file");
                $this->info("   File ID: " . $uploadResult['id']);
                $this->info("   File Name: " . $uploadResult['name']);
                $this->info("   View Link: " . $uploadResult['webViewLink']);
                
                // Clean up test file
                unlink($testFilePath);
                
            } catch (Exception $e) {
                $this->error("❌ Failed to upload file");
                $this->error("   Error: " . $e->getMessage());
                
                // Clean up test file
                if (file_exists($testFilePath)) {
                    unlink($testFilePath);
                }
                
                return 1;
            }
        } else {
            $this->info('Step 5: Skipping file upload test');
            $this->info('   Use --upload-test flag to test file upload');
        }
        
        $this->newLine();
        $this->info('✅ All tests passed!');
        $this->newLine();
        
        $this->info('Next steps:');
        $this->line('1. Ensure GOOGLE_DRIVE_ENABLED=true in your .env');
        $this->line('2. Try uploading a document through the UI');
        $this->line('3. Check storage/logs/laravel.log for any errors');
        $this->newLine();
        
        $this->info('To test file upload:');
        $this->line('   php artisan test:google-drive --upload-test');
        
        return 0;
    }
}
