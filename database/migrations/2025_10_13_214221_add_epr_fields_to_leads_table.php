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
            // EPR Fields
            $table->json('epr_measures')->nullable()->after('epr_report');
            $table->string('epr_pre_rating')->nullable()->after('epr_measures');
            $table->decimal('epr_abs', 10, 2)->nullable()->after('epr_pre_rating');
            $table->decimal('epr_amount_funded', 10, 2)->nullable()->after('epr_abs');
            $table->json('epr_payments')->nullable()->after('epr_amount_funded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'epr_measures',
                'epr_pre_rating',
                'epr_abs',
                'epr_amount_funded',
                'epr_payments',
            ]);
        });
    }
};
