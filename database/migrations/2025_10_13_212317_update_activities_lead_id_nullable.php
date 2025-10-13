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
        Schema::table('activities', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['lead_id']);
            
            // Make lead_id nullable
            $table->uuid('lead_id')->nullable()->change();
            
            // Re-add foreign key with nullOnDelete
            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['lead_id']);
            
            // Make lead_id not nullable
            $table->uuid('lead_id')->nullable(false)->change();
            
            // Re-add foreign key with cascade delete
            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->onDelete('cascade');
        });
    }
};
