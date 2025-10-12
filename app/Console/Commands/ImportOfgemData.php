<?php

namespace App\Console\Commands;

use App\Models\Eco4PartialScore;
use App\Models\Eco4FullScore;
use App\Models\GbisPartialScore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportOfgemData extends Command
{
    protected $signature = 'ofgem:import {--fresh : Drop existing data before importing}';
    protected $description = 'Import Ofgem ECO4 and GBIS score matrices from CSV files';

    public function handle()
    {
        if ($this->option('fresh')) {
            $this->warn('Truncating existing data...');
            DB::table('eco4_partial_scores')->truncate();
            DB::table('eco4_full_scores')->truncate();
            DB::table('gbis_partial_scores')->truncate();
        }

        $this->info('Starting Ofgem data import...');
        
        // Import ECO4 Partial Scores
        $this->importEco4Partial();
        
        // Import ECO4 Full Scores
        $this->importEco4Full();
        
        // Import GBIS Partial Scores
        $this->importGbisPartial();
        
        $this->info('Import complete!');
        $this->displayStats();
    }

    private function importEco4Partial()
    {
        $file = storage_path('ofgem_files/eco4_partial_v6.csv');
        
        if (!file_exists($file)) {
            $this->error("ECO4 Partial file not found: $file");
            return;
        }

        $this->info('Importing ECO4 Partial Scores...');
        
        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle); // Skip header row
        $headers = fgetcsv($handle); // Get actual headers
        
        $count = 0;
        $batch = [];
        $batchSize = 500;
        
        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty(array_filter($row))) continue;
            
            $batch[] = [
                'measure_category' => $row[0] ?? null,
                'measure_type' => $row[1] ?? null,
                'pre_main_heating_source' => $row[2] ?? null,
                'post_main_heating_source' => $row[3] ?? null,
                'floor_area_band' => $row[4] ?? null,
                'starting_band' => $row[5] ?? null,
                'average_treatable_factor' => is_numeric($row[6] ?? null) ? $row[6] : null,
                'cost_savings' => is_numeric($row[7] ?? null) ? $row[7] : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            if (count($batch) >= $batchSize) {
                Eco4PartialScore::insert($batch);
                $count += count($batch);
                $batch = [];
                $this->output->write("\rImported $count rows...");
            }
        }
        
        // Insert remaining
        if (!empty($batch)) {
            Eco4PartialScore::insert($batch);
            $count += count($batch);
        }
        
        fclose($handle);
        $this->info("\n✓ Imported $count ECO4 Partial Scores");
    }

    private function importEco4Full()
    {
        $file = storage_path('ofgem_files/ECO4 Full Project Scores Matrix.csv');
        
        if (!file_exists($file)) {
            $this->warn("ECO4 Full file not found: $file");
            return;
        }

        $this->info('Importing ECO4 Full Scores...');
        
        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle); // Skip header
        
        $count = 0;
        $batch = [];
        $batchSize = 500;
        
        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) continue;
            
            $batch[] = [
                'floor_area_band' => $row[0] ?? null,
                'starting_band' => $row[1] ?? null,
                'finishing_band' => $row[2] ?? null,
                'cost_savings' => is_numeric($row[3] ?? null) ? $row[3] : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            if (count($batch) >= $batchSize) {
                Eco4FullScore::insert($batch);
                $count += count($batch);
                $batch = [];
                $this->output->write("\rImported $count rows...");
            }
        }
        
        if (!empty($batch)) {
            Eco4FullScore::insert($batch);
            $count += count($batch);
        }
        
        fclose($handle);
        $this->info("\n✓ Imported $count ECO4 Full Scores");
    }

    private function importGbisPartial()
    {
        $file = storage_path('ofgem_files/gbis_partial_v3.csv');
        
        if (!file_exists($file)) {
            $this->error("GBIS Partial file not found: $file");
            return;
        }

        $this->info('Importing GBIS Partial Scores...');
        
        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle); // Skip header row
        $headers = fgetcsv($handle); // Get actual headers
        
        $count = 0;
        $batch = [];
        $batchSize = 500;
        
        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) continue;
            
            $batch[] = [
                'measure_category' => $row[0] ?? null,
                'measure_type' => $row[1] ?? null,
                'pre_main_heating_source' => $row[2] ?? null,
                'floor_area_band' => $row[3] ?? null,
                'starting_band' => $row[4] ?? null,
                'average_treatable_factor' => is_numeric($row[5] ?? null) ? $row[5] : null,
                'cost_savings' => is_numeric($row[6] ?? null) ? $row[6] : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            if (count($batch) >= $batchSize) {
                GbisPartialScore::insert($batch);
                $count += count($batch);
                $batch = [];
                $this->output->write("\rImported $count rows...");
            }
        }
        
        if (!empty($batch)) {
            GbisPartialScore::insert($batch);
            $count += count($batch);
        }
        
        fclose($handle);
        $this->info("\n✓ Imported $count GBIS Partial Scores");
    }

    private function displayStats()
    {
        $this->newLine();
        $this->info('==============================================');
        $this->info('Database Statistics:');
        $this->info('==============================================');
        $this->line('ECO4 Partial Scores: ' . Eco4PartialScore::count());
        $this->line('ECO4 Full Scores: ' . Eco4FullScore::count());
        $this->line('GBIS Partial Scores: ' . GbisPartialScore::count());
        $this->info('==============================================');
    }
}

