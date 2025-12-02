<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;

class MigrateExpenseTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expenses:migrate-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing expense types: TRV/TTZC -> Gas Engineer, Extractor Fan -> Loft Material';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting expense type migration...');

        $leads = Lead::whereNotNull('epr_payments')->get();
        $updatedCount = 0;
        $trvCount = 0;
        $extractorCount = 0;

        foreach ($leads as $lead) {
            if (empty($lead->epr_payments) || !is_array($lead->epr_payments)) {
                continue;
            }

            $updated = false;
            $payments = $lead->epr_payments;

            foreach ($payments as $index => $payment) {
                if (!isset($payment['type'])) {
                    continue;
                }

                // Migrate TRV/TTZC to Gas Engineer
                if ($payment['type'] === 'TRV/TTZC') {
                    $payments[$index]['type'] = 'Gas Engineer';
                    $updated = true;
                    $trvCount++;
                }

                // Migrate Extractor Fan to Loft Material
                if ($payment['type'] === 'Extractor Fan') {
                    $payments[$index]['type'] = 'Loft Material';
                    $updated = true;
                    $extractorCount++;
                }
            }

            if ($updated) {
                $lead->epr_payments = $payments;
                $lead->save();
                $updatedCount++;
            }
        }

        $this->info("Migration completed!");
        $this->info("Updated {$updatedCount} leads");
        $this->info("Migrated {$trvCount} TRV/TTZC entries to Gas Engineer");
        $this->info("Migrated {$extractorCount} Extractor Fan entries to Loft Material");

        return Command::SUCCESS;
    }
}
