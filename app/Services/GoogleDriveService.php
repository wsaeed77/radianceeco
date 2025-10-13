<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleDriveService
{
    protected $client;
    protected $service;
    protected $rootFolderId;

    public function __construct()
    {
        try {
            $this->client = new Client();
            $this->client->setApplicationName(config('app.name'));
            $this->client->setScopes([Drive::DRIVE_FILE]);
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
            
            $authType = config('services.google_drive.auth_type', 'service_account');
            
            if ($authType === 'oauth') {
                // OAuth authentication
                $this->client->setAuthConfig(storage_path('app/google-oauth-credentials.json'));
                
                // Load previously authorized token
                $tokenPath = storage_path('app/google-oauth-token.json');
                if (file_exists($tokenPath)) {
                    $accessToken = json_decode(file_get_contents($tokenPath), true);
                    $this->client->setAccessToken($accessToken);
                }
                
                // Refresh token if expired
                if ($this->client->isAccessTokenExpired()) {
                    if ($this->client->getRefreshToken()) {
                        $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                        file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
                    } else {
                        throw new Exception('OAuth token expired and no refresh token available. Run: php artisan google:authorize');
                    }
                }
            } else {
                // Service account authentication (legacy)
                $this->client->setAuthConfig(storage_path('app/google-drive-credentials.json'));
            }
            
            $this->service = new Drive($this->client);
            $this->rootFolderId = config('services.google_drive.root_folder_id');
        } catch (Exception $e) {
            Log::error('Google Drive Service initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get or create a folder for a lead
     */
    public function getOrCreateLeadFolder($leadId, $leadName)
    {
        try {
            $folderName = "Lead_{$leadId}_{$leadName}";
            
            // Search for existing folder
            $query = "name='{$folderName}' and mimeType='application/vnd.google-apps.folder' and trashed=false";
            if ($this->rootFolderId) {
                $query .= " and '{$this->rootFolderId}' in parents";
            }
            
            $results = $this->service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)',
                'spaces' => 'drive',
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ]);

            if (count($results->getFiles()) > 0) {
                return $results->getFiles()[0]->getId();
            }

            // Create new folder
            $fileMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            if ($this->rootFolderId) {
                $fileMetadata->setParents([$this->rootFolderId]);
            }

            $folder = $this->service->files->create($fileMetadata, [
                'fields' => 'id',
                'supportsAllDrives' => true,
            ]);

            return $folder->id;
        } catch (Exception $e) {
            Log::error('Failed to create lead folder: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get or create a document type folder inside a lead folder
     */
    public function getOrCreateDocumentTypeFolder($parentFolderId, $documentType)
    {
        try {
            $folderName = ucwords(str_replace('_', ' ', $documentType));
            
            // Search for existing folder
            $query = "name='{$folderName}' and mimeType='application/vnd.google-apps.folder' and '{$parentFolderId}' in parents and trashed=false";
            
            $results = $this->service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)',
                'spaces' => 'drive',
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ]);

            if (count($results->getFiles()) > 0) {
                return $results->getFiles()[0]->getId();
            }

            // Create new folder
            $fileMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parentFolderId]
            ]);

            $folder = $this->service->files->create($fileMetadata, [
                'fields' => 'id',
                'supportsAllDrives' => true,
            ]);

            return $folder->id;
        } catch (Exception $e) {
            Log::error('Failed to create document type folder: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload a file to Google Drive
     */
    public function uploadFile($filePath, $fileName, $folderId, $mimeType = null)
    {
        try {
            if (!file_exists($filePath)) {
                throw new Exception("File not found: {$filePath}");
            }

            // Get folder metadata to check if it's in a shared drive
            $folder = $this->service->files->get($folderId, [
                'fields' => 'id, name, driveId, capabilities',
                'supportsAllDrives' => true,
            ]);

            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => [$folderId]
            ]);

            $content = file_get_contents($filePath);
            
            $uploadParams = [
                'data' => $content,
                'mimeType' => $mimeType ?? mime_content_type($filePath),
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink, webContentLink',
                'supportsAllDrives' => true,
            ];

            // If in a shared drive, specify it
            if (isset($folder->driveId)) {
                $uploadParams['driveId'] = $folder->driveId;
            }

            $file = $this->service->files->create($fileMetadata, $uploadParams);

            return [
                'id' => $file->id,
                'name' => $file->name,
                'webViewLink' => $file->webViewLink,
                'webContentLink' => $file->webContentLink ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Failed to upload file to Google Drive: ' . $e->getMessage());
            Log::error('File path: ' . $filePath);
            Log::error('Folder ID: ' . $folderId);
            throw $e;
        }
    }

    /**
     * Delete a file from Google Drive
     */
    public function deleteFile($fileId)
    {
        try {
            $this->service->files->delete($fileId);
            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete file from Google Drive: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get file metadata
     */
    public function getFile($fileId)
    {
        try {
            return $this->service->files->get($fileId, [
                'fields' => 'id, name, mimeType, size, createdTime, modifiedTime, webViewLink'
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get file metadata: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if service is properly configured
     */
    public function isConfigured()
    {
        $authType = config('services.google_drive.auth_type', 'service_account');
        
        if ($authType === 'oauth') {
            return file_exists(storage_path('app/google-oauth-credentials.json')) 
                && file_exists(storage_path('app/google-oauth-token.json'));
        }
        
        return file_exists(storage_path('app/google-drive-credentials.json'));
    }
    
    /**
     * Get the Google Client instance
     */
    public function getClient()
    {
        return $this->client;
    }
}

