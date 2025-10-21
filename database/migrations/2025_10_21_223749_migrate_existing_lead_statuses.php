<?php

use App\Models\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

        // Update leads to use status_id instead of status enum
        foreach ($statusMapping as $oldStatus => $newSlug) {
            $status = Status::where('slug', $newSlug)->first();
            if ($status) {
                DB::table('leads')
                    ->where('status', $oldStatus)
                    ->update(['status_id' => $status->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Map status slugs back to old enum values
        $statusMapping = [
            'new' => 'new',
            'hold' => 'hold',
            'not-possible' => 'not_possible',
            'need-to-visit-property' => 'need_to_visit_property',
            'property-visited' => 'property_visited',
            'survey-booked' => 'survey_booked',
            'survey-done' => 'survey_done',
            'data-updated-in-google-drive' => 'data_updated_in_google_drive',
            'need-to-send-data-match' => 'need_to_send_data_match',
            'data-match-sent' => 'data_match_sent',
            'need-to-book-installation' => 'need_to_book_installation',
            'installation-booked' => 'installation_booked',
            'property-installed' => 'property_installed',
            'unknown' => 'unknown',
        ];

        // Update leads to use status enum instead of status_id
        foreach ($statusMapping as $slug => $oldStatus) {
            $status = Status::where('slug', $slug)->first();
            if ($status) {
                DB::table('leads')
                    ->where('status_id', $status->id)
                    ->update(['status' => $oldStatus]);
            }
        }
    }
};
