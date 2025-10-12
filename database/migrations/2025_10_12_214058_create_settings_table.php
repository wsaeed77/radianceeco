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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, float, boolean, json
            $table->string('group')->default('general'); // general, eco4, system, etc.
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'eco4_pps_eco_rate',
                'value' => '21.0',
                'type' => 'float',
                'group' => 'eco4',
                'label' => 'PPS ECO Rate',
                'description' => 'The rate used to calculate ECO value from PPS points (£/£ ABS)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'eco4_innovation_multiplier',
                'value' => '1.0',
                'type' => 'float',
                'group' => 'eco4',
                'label' => 'Innovation Multiplier',
                'description' => 'Multiplier applied to innovative measures',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
