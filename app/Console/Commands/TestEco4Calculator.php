<?php

namespace App\Console\Commands;

use App\Services\Eco4CalculatorService;
use Illuminate\Console\Command;

class TestEco4Calculator extends Command
{
    protected $signature = 'test:eco4-calculator';
    protected $description = 'Test the ECO4 calculator service with sample data';

    public function handle(Eco4CalculatorService $calculator)
    {
        $this->info('🧮 Testing ECO4 Calculator...');
        $this->newLine();
        
        // Test 1: GBIS Partial Calculation
        $this->info('Test 1: GBIS Partial Calculation');
        $this->info('=====================================');
        
        $gbisData = [
            'scheme' => 'GBIS',
            'starting_sap_band' => 'High_D',
            'floor_area_band' => '0-72',
            'pps_eco_rate' => 21.5,
            'measures' => [
                [
                    'type' => 'CWI_0.040',
                    'percentage_treated' => 100,
                    'is_innovation' => false,
                ],
            ],
        ];
        
        $result1 = $calculator->calculate($gbisData);
        
        if ($result1['success']) {
            $this->info('✅ GBIS Calculation Successful');
            $this->line('   Total ABS: ' . $result1['summary']['total_abs']);
            $this->line('   Total ECO Value: £' . $result1['summary']['total_eco_value']);
            $this->line('   Measures: ' . count($result1['measures']));
            
            if (!empty($result1['measures'])) {
                $this->line('   First Measure:');
                $measure = $result1['measures'][0];
                $this->line('     - Type: ' . $measure['measure_type']);
                $this->line('     - ABS: ' . $measure['abs_value']);
                $this->line('     - ECO Value: £' . $measure['eco_value']);
            }
        } else {
            $this->error('❌ GBIS Calculation Failed: ' . ($result1['message'] ?? 'Unknown error'));
        }
        
        $this->newLine();
        
        // Test 2: ECO4 Partial Calculation
        $this->info('Test 2: ECO4 Partial Calculation');
        $this->info('=====================================');
        
        $eco4Data = [
            'scheme' => 'ECO4',
            'starting_sap_band' => 'Low_E',
            'floor_area_band' => '73-97',
            'pre_main_heat_source' => 'Condensing Gas Boiler',
            'pps_eco_rate' => 21.5,
            'measures' => [
                [
                    'type' => 'Loft_Insulation_0.16',
                    'percentage_treated' => 100,
                    'is_innovation' => false,
                ],
                [
                    'type' => 'CWI_0.040',
                    'percentage_treated' => 100,
                    'is_innovation' => false,
                ],
            ],
        ];
        
        $result2 = $calculator->calculate($eco4Data);
        
        if ($result2['success']) {
            $this->info('✅ ECO4 Calculation Successful');
            $this->line('   Total ABS: ' . $result2['summary']['total_abs']);
            $this->line('   Total ECO Value: £' . $result2['summary']['total_eco_value']);
            $this->line('   Measures: ' . count($result2['measures']));
        } else {
            $this->error('❌ ECO4 Calculation Failed: ' . ($result2['message'] ?? 'Unknown error'));
        }
        
        $this->newLine();
        
        // Test 3: ECO4 Full Project
        $this->info('Test 3: ECO4 Full Project Calculation');
        $this->info('=====================================');
        
        $fullData = [
            'scheme' => 'ECO4',
            'calculation_type' => 'full',
            'starting_sap_band' => 'High_E',
            'finishing_sap_band' => 'High_C',
            'floor_area_band' => '98-199',
            'pps_eco_rate' => 21.5,
        ];
        
        $result3 = $calculator->calculate($fullData);
        
        if ($result3['success']) {
            $this->info('✅ Full Project Calculation Successful');
            $this->line('   Cost Savings: ' . $result3['summary']['cost_savings']);
            $this->line('   ECO Value: £' . $result3['summary']['eco_value']);
        } else {
            $this->error('❌ Full Project Calculation Failed: ' . ($result3['message'] ?? 'Unknown error'));
        }
        
        $this->newLine();
        
        // Test 4: Get Metadata
        $this->info('Test 4: Get Metadata');
        $this->info('=====================================');
        
        $metadata = $calculator->getMetadata();
        $this->info('✅ Metadata Retrieved');
        $this->line('   Schemes: ' . implode(', ', $metadata['schemes']));
        $this->line('   SAP Bands: ' . count($metadata['sap_bands']));
        $this->line('   Floor Area Bands: ' . count($metadata['floor_area_bands']));
        $this->line('   Pre-heating Sources: ' . count($metadata['pre_main_heat_sources']));
        $this->line('   ECO4 Measure Categories: ' . count($metadata['measures']['eco4']));
        $this->line('   GBIS Measure Categories: ' . count($metadata['measures']['gbis']));
        
        $this->newLine();
        $this->info('🎉 All tests completed!');
        
        return 0;
    }
}

