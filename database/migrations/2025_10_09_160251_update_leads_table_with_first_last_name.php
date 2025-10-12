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
            // Add first_name and last_name columns
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            
            // Update address fields to match our models
            $table->string('address_line_1')->nullable()->after('email');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('state')->nullable()->after('city');
            $table->string('zip_code')->nullable()->after('state');
            
            // Add source fields
            $table->string('source')->nullable()->after('stage');
            $table->text('source_details')->nullable()->after('source');
            $table->text('notes')->nullable()->after('source_details');
            
            // Add flags
            $table->boolean('is_duplicate')->default(false)->after('notes');
            $table->boolean('is_complete')->default(false)->after('is_duplicate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'address_line_1',
                'address_line_2',
                'state',
                'zip_code',
                'source',
                'source_details',
                'notes',
                'is_duplicate',
                'is_complete'
            ]);
        });
    }
};
