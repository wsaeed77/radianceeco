<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GbisPartialScore;
use App\Services\Eco4CalculatorService;

class TestPrtMeasure extends Command
{
    protected $signature = 'test:prt';
    protected $description = 'Test Programmer and Room Thermostat calculation';

    public function handle()
    {
        $this->info('Testing Programmer and Room Thermostat (P&RT) in GBIS...');
        
        // Test direct database query
        $this->info("\n1. Direct Database Query:");
        $score = GbisPartialScore::where('measure_type', 'P&RT')
            ->where('floor_area_band', '0-72')
            ->where('starting_band', 'Low_D')
            ->where('pre_main_heating_source', 'Condensing Gas Boiler')
            ->first();
            
        if ($score) {
            $this->info("✓ Found score in database:");
            $this->info("  Measure Type: {$score->measure_type}");
            $this->info("  Floor Area: {$score->floor_area_band}");
            $this->info("  Starting Band: {$score->starting_band}");
            $this->info("  Pre-main Heating: {$score->pre_main_heating_source}");
            $this->info("  ABS: {$score->cost_savings}");
        } else {
            $this->error("✗ No score found!");
            
            // Try to find what's in the database
            $this->info("\nSearching for P&RT measures...");
            $measures = GbisPartialScore::where('measure_type', 'P&RT')
                ->select('measure_type', 'floor_area_band', 'starting_band', 'pre_main_heating_source')
                ->distinct()
                ->take(5)
                ->get();
                
            foreach ($measures as $m) {
                $this->info("  - {$m->measure_type} | {$m->floor_area_band} | {$m->starting_band} | {$m->pre_main_heating_source}");
            }
        }
        
        // Test via service
        $this->info("\n2. Via Calculator Service:");
        $service = app(Eco4CalculatorService::class);
        $result = $service->calculate([
            'scheme' => 'GBIS',
            'starting_sap_band' => 'D',
            'starting_sap_score' => 55,  // Low_D range
            'floor_area_band' => '0-72',
            'pre_main_heat_source' => 'Condensing Gas Boiler',
            'measures' => [
                ['type' => 'P&RT', 'percentage_treated' => 100, 'is_innovation' => false]
            ]
        ]);
        
        if ($result['success'] && !empty($result['measures'])) {
            $measure = $result['measures'][0];
            $this->info("✓ Calculation successful:");
            $this->info("  Measure: {$measure['measure_type']}");
            $this->info("  ABS: {$measure['abs_value']}");
            $this->info("  PPS: {$measure['pps_points']}");
            $this->info("  ECO Value: £{$measure['eco_value']}");
            
            // Compare with expected value
            $this->info("\n3. Comparison with CoreLogic:");
            $this->info("  Expected ABS: 0.66");
            $this->info("  Actual ABS: {$measure['abs_value']}");
            $match = abs($measure['abs_value'] - 0.66) < 0.01 ? '✓ MATCH' : '✗ MISMATCH';
            $this->info("  Result: {$match}");
        } else {
            $this->error("✗ Calculation failed:");
            if (!empty($result['measures'][0]['error'])) {
                $this->error("  " . $result['measures'][0]['error']);
                $this->info("  Criteria used: " . json_encode($result['measures'][0]['criteria'] ?? []));
            } else {
                $this->error("  " . ($result['message'] ?? 'Unknown error'));
            }
        }
        
        return 0;
    }
}
