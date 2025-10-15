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
            $table->unsignedSmallInteger('epr_pre_rating_score')->nullable()->after('epr_pre_rating');
            $table->unsignedSmallInteger('epr_post_rating_score')->nullable()->after('epr_post_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['epr_pre_rating_score', 'epr_post_rating_score']);
        });
    }
};


