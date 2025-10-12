<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ConvertOfgemFiles extends Command
{
    protected $signature = 'ofgem:convert';
    protected $description = 'Convert Ofgem Excel files to CSV';

    public function handle()
    {
        $this->info('Converting Ofgem Excel files to CSV...');
        
        $files = [
            'storage/ofgem_files/ECO4_Partial_Project_Scores_Matrix_v6.xlsx' => 'storage/ofgem_files/eco4_partial_v6.csv',
            'storage/ofgem_files/Great British Insulation Scheme Scores Matrix v.3.xlsx' => 'storage/ofgem_files/gbis_partial_v3.csv',
        ];
        
        foreach ($files as $excelFile => $csvFile) {
            if (!file_exists($excelFile)) {
                $this->error("File not found: $excelFile");
                continue;
            }
            
            $this->info("Converting: $excelFile");
            
            try {
                $spreadsheet = IOFactory::load($excelFile);
                $worksheet = $spreadsheet->getActiveSheet();
                
                // Get all rows
                $rows = [];
                foreach ($worksheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    
                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $rowData[] = $cell->getValue();
                    }
                    $rows[] = $rowData;
                }
                
                // Write to CSV
                $fp = fopen($csvFile, 'w');
                foreach ($rows as $row) {
                    fputcsv($fp, $row);
                }
                fclose($fp);
                
                $this->info("✓ Created: $csvFile (" . count($rows) . " rows)");
                
                // Show first 3 rows
                $this->line("\nFirst 3 rows:");
                for ($i = 0; $i < min(3, count($rows)); $i++) {
                    $this->line(implode(' | ', array_slice($rows[$i], 0, 8)));
                }
                $this->newLine();
                
            } catch (\Exception $e) {
                $this->error("Error converting $excelFile: " . $e->getMessage());
            }
        }
        
        $this->info('Conversion complete!');
        return 0;
    }
}

