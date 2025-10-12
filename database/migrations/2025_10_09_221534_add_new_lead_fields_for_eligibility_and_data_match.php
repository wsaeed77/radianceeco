<?php

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
        Schema::table('leads', function (Blueprint $table) {
            // Add client_dob to Eligibility section
            $table->date('eligibility_client_dob')->nullable();
            
            // Add grant_type field to Lead Status section
            $table->string('grant_type')->nullable();
            
            // Add Data Match fields
            $table->string('data_match_status')->nullable();
            $table->text('data_match_remarks')->nullable();
            
            // We already have benefit_holder_name and benefit_holder_dob fields
            // But we'll add multi_phone_numbers for storing multiple phone numbers with labels
            $table->json('multi_phone_numbers')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'eligibility_client_dob',
                'grant_type',
                'data_match_status',
                'data_match_remarks',
                'multi_phone_numbers',
            ]);
        });
    }
};
