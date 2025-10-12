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
            $table->string('google_drive_file_id')->nullable()->after('path');
            $table->string('google_drive_folder_id')->nullable()->after('google_drive_file_id');
            $table->text('google_drive_web_view_link')->nullable()->after('google_drive_folder_id');
            $table->text('google_drive_web_content_link')->nullable()->after('google_drive_web_view_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'google_drive_file_id',
                'google_drive_folder_id',
                'google_drive_web_view_link',
                'google_drive_web_content_link'
            ]);
        });
    }
};
