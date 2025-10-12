<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DeduplicateLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deduplicate-leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute dedupe keys and flag duplicate leads';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting lead deduplication process...');
        
        // Step 1: Recompute dedupe keys for all leads
        $this->recomputeDedupeKeys();
        
        // Step 2: Find and report duplicates
        $duplicates = $this->findDuplicates();
        
        // Step 3: Find incomplete leads
        $incomplete = $this->findIncompleteLeads();
        
        // Summary
        $this->info('Deduplication process completed.');
        $this->info('Duplicate leads found: ' . $duplicates->count());
        $this->info('Incomplete leads found: ' . $incomplete->count());
        
        // Output details if duplicates found
        if ($duplicates->count() > 0) {
            $this->table(
                ['Dedupe Key', 'Count', 'Lead IDs'],
                $duplicates->map(function ($item) {
                    return [
                        $item->dedupe_key,
                        $item->count,
                        $item->lead_ids
                    ];
                })->toArray()
            );
        }
        
        return 0;
    }
    
    /**
     * Recompute dedupe keys for all leads.
     */
    protected function recomputeDedupeKeys(): void
    {
        $this->info('Recomputing dedupe keys...');
        
        $count = 0;
        $batchSize = 100;
        
        // Process in batches to avoid memory issues
        Lead::chunk($batchSize, function ($leads) use (&$count) {
            foreach ($leads as $lead) {
                $lead->dedupe_key = Lead::generateDedupeKey($lead);
                $lead->save();
                $count++;
            }
            
            $this->output->write('.');
        });
        
        $this->info("\nRecomputed {$count} dedupe keys.");
    }
    
    /**
     * Find duplicate leads.
     */
    protected function findDuplicates(): Collection
    {
        $this->info('Finding duplicates...');
        
        // Find all dedupe keys with more than one occurrence
        return DB::table('leads')
            ->select('dedupe_key', DB::raw('COUNT(*) as count'), DB::raw('GROUP_CONCAT(id) as lead_ids'))
            ->whereNotNull('dedupe_key')
            ->groupBy('dedupe_key')
            ->having('count', '>', 1)
            ->get();
    }
    
    /**
     * Find incomplete leads.
     */
    protected function findIncompleteLeads(): Collection
    {
        $this->info('Finding incomplete leads...');
        
        // Find leads with missing important data
        return Lead::where(function ($query) {
                $query->whereNull('client_name')
                    ->orWhereNull('postcode')
                    ->orWhereNull('house_number');
            })
            ->get();
    }
}
