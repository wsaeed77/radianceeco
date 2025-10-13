<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class GoogleAuthorize extends Command
{
    protected $signature = 'google:authorize {--force : Force re-authorization}';
    protected $description = 'Authorize Google Drive OAuth access';

    public function handle()
    {
        $this->info('🔐 Google Drive OAuth Authorization');
        $this->newLine();

        $credentialsPath = storage_path('app/google-oauth-credentials.json');
        $tokenPath = storage_path('app/google-oauth-token.json');

        // Check if credentials file exists
        if (!file_exists($credentialsPath)) {
            $this->error('❌ OAuth credentials file not found!');
            $this->newLine();
            $this->info('Please create OAuth credentials in Google Cloud Console:');
            $this->line('1. Go to: https://console.cloud.google.com/apis/credentials');
            $this->line('2. Create OAuth 2.0 Client ID');
            $this->line('3. Download as JSON');
            $this->line('4. Save to: ' . $credentialsPath);
            $this->newLine();
            $this->info('See GOOGLE_DRIVE_OAUTH_SETUP.md for detailed instructions');
            return 1;
        }

        $this->info('✓ OAuth credentials found');

        // Check if already authorized
        if (file_exists($tokenPath) && !$this->option('force')) {
            $this->warn('⚠️  Already authorized!');
            $this->info('   Token file exists at: ' . $tokenPath);
            $this->newLine();
            
            if (!$this->confirm('Do you want to re-authorize?', false)) {
                $this->info('Authorization cancelled');
                return 0;
            }
        }

        // Initialize client
        try {
            $client = new Client();
            $client->setApplicationName(config('app.name'));
            $client->setScopes([Drive::DRIVE_FILE]);
            $client->setAuthConfig($credentialsPath);
            $client->setAccessType('offline');
            $client->setPrompt('consent');
            
            // Set redirect URI (for local development)
            $redirectUri = $this->ask('Enter redirect URI (or press Enter for http://localhost:8000/google/callback)', 'http://localhost:8000/google/callback');
            $client->setRedirectUri($redirectUri);
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to initialize Google Client');
            $this->error('   Error: ' . $e->getMessage());
            return 1;
        }

        // Generate auth URL
        $authUrl = $client->createAuthUrl();

        $this->newLine();
        $this->info('📋 Authorization Steps:');
        $this->line('1. Open this URL in your browser:');
        $this->newLine();
        $this->line($authUrl);
        $this->newLine();
        $this->line('2. Sign in with your Google account');
        $this->line('3. Grant permission to access Google Drive');
        $this->line('4. Copy the authorization code from the URL');
        $this->newLine();

        // Get authorization code from user
        $authCode = $this->ask('Paste the authorization code here');

        if (empty($authCode)) {
            $this->error('❌ No authorization code provided');
            return 1;
        }

        // Exchange authorization code for access token
        try {
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            if (array_key_exists('error', $accessToken)) {
                $this->error('❌ Error fetching access token');
                $this->error('   ' . $accessToken['error_description'] ?? $accessToken['error']);
                return 1;
            }

            // Save token to file
            file_put_contents($tokenPath, json_encode($accessToken));
            
            $this->newLine();
            $this->info('✅ Authorization successful!');
            $this->info('   Token saved to: ' . $tokenPath);
            $this->newLine();
            
            // Test the connection
            $this->info('🧪 Testing connection...');
            
            $service = new Drive($client);
            $results = $service->files->listFiles([
                'pageSize' => 1,
                'fields' => 'files(id, name)'
            ]);
            
            $this->info('✓ Successfully connected to Google Drive');
            $this->newLine();
            
            $this->info('Next steps:');
            $this->line('1. Update your .env file:');
            $this->line('   GOOGLE_DRIVE_ENABLED=true');
            $this->line('   GOOGLE_DRIVE_AUTH_TYPE=oauth');
            $this->newLine();
            $this->line('2. Test upload:');
            $this->line('   php artisan test:google-drive --upload-test');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Authorization failed');
            $this->error('   Error: ' . $e->getMessage());
            return 1;
        }
    }
}
