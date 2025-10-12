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
            // Add user_id column
            $table->foreignId('user_id')->nullable()->after('lead_id')->constrained('users')->nullOnDelete();
            
            // Add description column
            $table->text('description')->nullable()->after('type');
            
            // Make message nullable
            $table->text('message')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Drop the new columns
            $table->dropColumn('user_id');
            $table->dropColumn('description');
            
            // Make message required again
            $table->text('message')->nullable(false)->change();
        });
    }
};
