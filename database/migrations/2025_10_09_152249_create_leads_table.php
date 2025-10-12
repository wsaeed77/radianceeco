<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\LeadStatus;
use App\Enums\LeadStage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            
            // Client Information
            $table->string('client_name');
            $table->date('client_dob')->nullable();
            $table->string('client_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            
            // Address
            $table->string('house_number')->nullable();
            $table->string('street_name')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('address_line')->nullable();
            
            // Status and Stage
            $table->string('status_raw')->nullable();
            $table->string('stage_raw')->nullable();
            $table->text('status_notes_raw')->nullable();
            $table->string('status')->default(LeadStatus::UNKNOWN->value);
            $table->string('stage')->default(LeadStage::UNKNOWN->value);
            
            // Business metadata
            $table->string('grant')->nullable();
            $table->string('job_categories')->nullable();
            $table->string('possible_grant')->nullable();
            $table->string('benefit')->nullable();
            $table->string('poa')->nullable();
            $table->string('epc')->nullable();
            $table->string('gas_safe')->nullable();
            $table->string('council_tax_band')->nullable();
            $table->string('epr_report')->nullable();
            
            // Benefit holder info
            $table->string('benefit_holder_name')->nullable();
            $table->date('benefit_holder_dob')->nullable();
            
            // Agent info
            $table->string('agent')->nullable();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Deduplication
            $table->string('dedupe_key')->nullable()->unique();
            
            // Indexes
            $table->index(['status']);
            $table->index(['stage']);
            $table->index(['client_name']);
            $table->index(['postcode']);
            $table->index(['agent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
