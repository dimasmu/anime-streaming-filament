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
        Schema::rename('video_upload_types', 'video_upload_sources');

        Schema::table('episodes', function (Blueprint $table) {
            $table->renameColumn('video_upload_type_id', 'video_upload_source_id');
            $table->renameIndex('episodes_video_upload_type_id_foreign', 'episodes_video_upload_source_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('video_upload_sources', 'video_upload_types');

        Schema::table('episodes', function (Blueprint $table) {
            $table->renameColumn('video_upload_source_id', 'video_upload_type_id');
            $table->renameIndex('episodes_video_upload_source_id_foreign', 'episodes_video_upload_type_id_foreign');
        });
    }
};
