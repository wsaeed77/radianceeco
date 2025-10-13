<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Drive;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleSheetsService
{
    protected $client;
    protected $sheetsService;
    protected $driveService;

    public function __construct()
    {
        try {
            $this->client = new Client();
            $this->client->setApplicationName(config('app.name'));
            $this->client->setScopes([
                Sheets::SPREADSHEETS_READONLY,
                Drive::DRIVE_READONLY
            ]);
            $this->client->setAuthConfig(storage_path('app/google-drive-credentials.json'));
            $this->client->setAccessType('offline');
            
            $this->sheetsService = new Sheets($this->client);
            $this->driveService = new Drive($this->client);
        } catch (Exception $e) {
            Log::error('Google Sheets Service initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * List all accessible Google Sheets
     */
    public function listSheets($pageSize = 50, $pageToken = null)
    {
        try {
            $params = [
                'pageSize' => $pageSize,
                'fields' => 'nextPageToken, files(id, name, modifiedTime, owners)',
                'q' => "mimeType='application/vnd.google-apps.spreadsheet' and trashed=false",
                'orderBy' => 'modifiedTime desc',
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ];

            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $results = $this->driveService->files->listFiles($params);

            return [
                'sheets' => collect($results->getFiles())->map(function ($file) {
                    return [
                        'id' => $file->getId(),
                        'name' => $file->getName(),
                        'modified_time' => $file->getModifiedTime(),
                        'owner' => $file->getOwners()[0]->getDisplayName() ?? 'Unknown',
                    ];
                })->toArray(),
                'next_page_token' => $results->getNextPageToken(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to list Google Sheets: ' . $e->getMessage());
            throw new Exception('Failed to retrieve Google Sheets list: ' . $e->getMessage());
        }
    }

    /**
     * Get sheet metadata and available sheets/tabs
     */
    public function getSheetInfo($spreadsheetId)
    {
        try {
            $spreadsheet = $this->sheetsService->spreadsheets->get($spreadsheetId);

            return [
                'id' => $spreadsheet->getSpreadsheetId(),
                'title' => $spreadsheet->getProperties()->getTitle(),
                'sheets' => collect($spreadsheet->getSheets())->map(function ($sheet) {
                    $props = $sheet->getProperties();
                    return [
                        'id' => $props->getSheetId(),
                        'title' => $props->getTitle(),
                        'index' => $props->getIndex(),
                        'row_count' => $props->getGridProperties()->getRowCount(),
                        'column_count' => $props->getGridProperties()->getColumnCount(),
                    ];
                })->toArray(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get sheet info: ' . $e->getMessage());
            throw new Exception('Failed to retrieve sheet information: ' . $e->getMessage());
        }
    }

    /**
     * Get data from a specific sheet/tab
     */
    public function getSheetData($spreadsheetId, $sheetName, $range = null)
    {
        try {
            // If no range specified, get all data
            $rangeString = $range ?? $sheetName;

            $response = $this->sheetsService->spreadsheets_values->get(
                $spreadsheetId,
                $rangeString
            );

            $values = $response->getValues();

            if (empty($values)) {
                return [
                    'headers' => [],
                    'rows' => [],
                    'total_rows' => 0,
                ];
            }

            // First row as headers
            $headers = array_shift($values);

            return [
                'headers' => $headers,
                'rows' => $values,
                'total_rows' => count($values),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get sheet data: ' . $e->getMessage());
            throw new Exception('Failed to retrieve sheet data: ' . $e->getMessage());
        }
    }

    /**
     * Preview first N rows of a sheet
     */
    public function previewSheet($spreadsheetId, $sheetName, $limit = 10)
    {
        try {
            $data = $this->getSheetData($spreadsheetId, $sheetName);

            return [
                'headers' => $data['headers'],
                'preview_rows' => array_slice($data['rows'], 0, $limit),
                'total_rows' => $data['total_rows'],
                'has_more' => $data['total_rows'] > $limit,
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get batch data for import (with pagination)
     */
    public function getBatchData($spreadsheetId, $sheetName, $offset = 0, $limit = 100)
    {
        try {
            $data = $this->getSheetData($spreadsheetId, $sheetName);

            $batchRows = array_slice($data['rows'], $offset, $limit);

            return [
                'headers' => $data['headers'],
                'rows' => $batchRows,
                'offset' => $offset,
                'limit' => $limit,
                'total_rows' => $data['total_rows'],
                'has_more' => ($offset + $limit) < $data['total_rows'],
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}

