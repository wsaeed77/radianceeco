<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->json('epc_recommendations')->nullable()->after('epc_data');
            $table->timestamp('epc_recommendations_fetched_at')->nullable()->after('epc_recommendations');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['epc_recommendations', 'epc_recommendations_fetched_at']);
        });
    }
};


