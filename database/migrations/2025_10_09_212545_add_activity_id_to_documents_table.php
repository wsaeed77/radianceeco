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
        Schema::table('documents', function (Blueprint $table) {
            // Add nullable activity_id column with foreign key
            $table->uuid('activity_id')->nullable()->after('lead_id');
            
            // Add foreign key constraint
            $table->foreign('activity_id')
                  ->references('id')
                  ->on('activities')
                  ->onDelete('set null');
                  
            // Add index
            $table->index('activity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['activity_id']);
            
            // Drop index
            $table->dropIndex(['activity_id']);
            
            // Drop column
            $table->dropColumn('activity_id');
        });
    }
};
