<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Check if the columns don't already exist
            if (!Schema::hasColumn('leads', 'occupancy_type')) {
                $table->string('occupancy_type')->nullable()->comment('owner/tenant');
            }
            
            if (!Schema::hasColumn('leads', 'possible_grant_types')) {
                $table->string('possible_grant_types')->nullable()->comment('Loft only/Loft+TRV+Thermostate/Boiler/Boiler+Loft');
            }
            
            if (!Schema::hasColumn('leads', 'benefit_type')) {
                $table->string('benefit_type')->nullable()->comment('Universal Credit,Child Benefit,Pension Credit,Child Tax Credit,Income Support,Job Seeker Allowance,No Benefit');
            }
            
            if (!Schema::hasColumn('leads', 'poa_info')) {
                $table->text('poa_info')->nullable()->comment('Proof of Address Information');
            }
            
            if (!Schema::hasColumn('leads', 'epc_rating')) {
                $table->string('epc_rating')->nullable()->comment('A,B,C,D,E,F');
            }
            
            if (!Schema::hasColumn('leads', 'epc_details')) {
                $table->text('epc_details')->nullable()->comment('EPC Details');
            }
            
            if (!Schema::hasColumn('leads', 'gas_safe_info')) {
                $table->string('gas_safe_info')->nullable();
            }
            
            // Don't re-add council_tax_band as it already exists
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $columnsToCheck = [
                'occupancy_type',
                'possible_grant_types',
                'benefit_type',
                'poa_info',
                'epc_rating',
                'epc_details',
                'gas_safe_info'
            ];
            
            $columnsToRemove = [];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('leads', $column)) {
                    $columnsToRemove[] = $column;
                }
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};