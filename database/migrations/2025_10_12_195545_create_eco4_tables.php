<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ECO4 Partial Scores Matrix
        Schema::create('eco4_partial_scores', function (Blueprint $table) {
            $table->id();
            $table->string('measure_category')->nullable();
            $table->string('measure_type');
            $table->string('pre_main_heating_source')->nullable();
            $table->string('post_main_heating_source')->nullable();
            $table->string('floor_area_band');
            $table->string('starting_band');
            $table->decimal('average_treatable_factor', 8, 4)->nullable();
            $table->decimal('cost_savings', 10, 2);
            $table->timestamps();
            
            // Indexes for faster lookups
            $table->index(['measure_type', 'floor_area_band', 'starting_band'], 'eco4_partial_measure_lookup');
            $table->index(['pre_main_heating_source', 'starting_band'], 'eco4_partial_heat_source');
        });

        // ECO4 Full Project Scores Matrix
        Schema::create('eco4_full_scores', function (Blueprint $table) {
            $table->id();
            $table->string('floor_area_band');
            $table->string('starting_band');
            $table->string('finishing_band');
            $table->decimal('cost_savings', 10, 2);
            $table->timestamps();
            
            $table->index(['floor_area_band', 'starting_band', 'finishing_band'], 'eco4_full_lookup');
        });

        // GBIS Partial Scores Matrix
        Schema::create('gbis_partial_scores', function (Blueprint $table) {
            $table->id();
            $table->string('measure_category')->nullable();
            $table->string('measure_type');
            $table->string('pre_main_heating_source')->nullable();
            $table->string('floor_area_band');
            $table->string('starting_band');
            $table->decimal('average_treatable_factor', 8, 4)->nullable();
            $table->decimal('cost_savings', 10, 2);
            $table->timestamps();
            
            $table->index(['measure_type', 'floor_area_band', 'starting_band'], 'gbis_partial_measure_lookup');
        });

        // ECO4 Calculations (saved per lead)
        Schema::create('eco4_calculations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lead_id')->constrained()->onDelete('cascade');
            $table->string('scheme'); // GBIS or ECO4
            $table->string('calculation_type'); // partial or full
            $table->integer('starting_sap_score')->nullable();
            $table->string('starting_sap_band');
            $table->integer('finishing_sap_score')->nullable();
            $table->string('finishing_sap_band')->nullable();
            $table->string('floor_area_band');
            $table->string('property_type')->nullable();
            $table->string('wall_type')->nullable();
            $table->string('country')->nullable();
            $table->string('pre_main_heat_source')->nullable();
            $table->string('post_main_heat_source')->nullable();
            $table->decimal('pps_eco_rate', 8, 2)->default(21.5); // £/£ ABS rate
            $table->decimal('innovation_multiplier', 8, 2)->default(1.0);
            $table->decimal('total_abs', 10, 2)->default(0);
            $table->decimal('total_eco_value', 10, 2)->default(0);
            $table->json('summary')->nullable();
            $table->timestamps();
        });

        // ECO4 Selected Measures
        Schema::create('eco4_measures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calculation_id')->constrained('eco4_calculations')->onDelete('cascade');
            $table->string('measure_type');
            $table->string('measure_variant')->nullable();
            $table->string('measure_category')->nullable();
            $table->string('post_heat_source')->nullable();
            $table->integer('percentage_treated')->default(100);
            $table->boolean('is_innovation_measure')->default(false);
            $table->decimal('abs_value', 10, 2); // Annual Bill Savings
            $table->decimal('pps_points', 10, 2); // Potential Points Score
            $table->decimal('eco_value', 10, 2); // Final ECO value
            $table->json('matrix_data')->nullable(); // Store the matched matrix row
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eco4_measures');
        Schema::dropIfExists('eco4_calculations');
        Schema::dropIfExists('gbis_partial_scores');
        Schema::dropIfExists('eco4_full_scores');
        Schema::dropIfExists('eco4_partial_scores');
    }
};
