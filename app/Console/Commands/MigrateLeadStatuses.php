<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\Status;
use Illuminate\Console\Command;

class MigrateLeadStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:lead-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing lead statuses to use status_id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting lead status migration...');

        // Map old enum values to new status slugs
        $statusMapping = [
            'new' => 'new',
            'hold' => 'hold',
            'not_possible' => 'not-possible',
            'need_to_visit_property' => 'need-to-visit-property',
            'property_visited' => 'property-visited',
            'survey_booked' => 'survey-booked',
            'survey_done' => 'survey-done',
            'data_updated_in_google_drive' => 'data-updated-in-google-drive',
            'need_to_send_data_match' => 'need-to-send-data-match',
            'data_match_sent' => 'data-match-sent',
            'need_to_book_installation' => 'need-to-book-installation',
            'installation_booked' => 'installation-booked',
            'property_installed' => 'property-installed',
            'unknown' => 'unknown',
        ];

        $totalUpdated = 0;

        foreach ($statusMapping as $oldStatus => $newSlug) {
            $status = Status::where('slug', $newSlug)->first();
            if ($status) {
                $count = Lead::where('status', $oldStatus)->update(['status_id' => $status->id]);
                $this->info("Updated {$count} leads from '{$oldStatus}' to status_id {$status->id}");
                $totalUpdated += $count;
            } else {
                $this->warn("Status with slug '{$newSlug}' not found for old status '{$oldStatus}'");
            }
        }

        $this->info("Migration completed! Total leads updated: {$totalUpdated}");
    }
}
