<?php

use App\Enums\LeadSource;
use App\Models\Lead;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $validSources = array_column(LeadSource::cases(), 'value');

        // Get all leads with sources not matching the enum values
        $leads = Lead::whereNotNull('source')
            ->whereNotIn('source', $validSources)
            ->get();

        // Update each lead's source to a valid value
        foreach ($leads as $lead) {
            // Default to Online if source doesn't match any valid option
            $lead->source = LeadSource::ONLINE;
            $lead->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot really reverse this change meaningfully
    }
};
