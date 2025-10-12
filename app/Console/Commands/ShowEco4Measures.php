<?php

namespace App\Console\Commands;

use App\Models\GbisPartialScore;
use Illuminate\Console\Command;

class ShowEco4Measures extends Command
{
    protected $signature = 'eco4:show-measures';
    protected $description = 'Show available ECO4/GBIS measures in database';

    public function handle()
    {
        $this->info('=== GBIS Measures ===');
        
        // Get distinct measure types
        $measures = GbisPartialScore::select('measure_type', 'pre_main_heating_source')
            ->distinct()
            ->orderBy('measure_type')
            ->limit(50)
            ->get();
        
        $this->table(
            ['Measure Type', 'Pre-main Heating Source'],
            $measures->map(fn($m) => [$m->measure_type, $m->pre_main_heating_source])->toArray()
        );
        
        $this->newLine();
        
        // Search for specific measures
        $this->info('Searching for Loft, Smart, TRV measures:');
        
        $specific = GbisPartialScore::where(function($q) {
            $q->where('measure_type', 'LIKE', '%Loft%')
              ->orWhere('measure_type', 'LIKE', '%Smart%')
              ->orWhere('measure_type', 'LIKE', '%TRV%');
        })->distinct()->pluck('measure_type');
        
        foreach ($specific as $measure) {
            $this->line("- $measure");
        }
    }
}

