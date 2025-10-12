<?php

namespace App\Console\Commands;

use App\Services\LeadImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class ImportLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-leads {file : The path to the CSV file} {--user-id= : The ID of the user performing the import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import leads from a CSV file';

    /**
     * Execute the console command.
     */
    public function handle(LeadImportService $importService)
    {
        $filePath = $this->argument('file');
        $userId = $this->option('user-id');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }
        
        $this->info('Starting import process...');
        
        $results = $importService->import($filePath, $userId);
        
        $this->info('Import completed:');
        $this->table(
            ['Status', 'Count'],
            [
                ['Imported (new)', $results['imported']],
                ['Updated', $results['updated']],
                ['Skipped', $results['skipped']],
                ['Failed', $results['failed']],
                ['Duplicates', $results['duplicates']],
            ]
        );
        
        return 0;
    }
}
