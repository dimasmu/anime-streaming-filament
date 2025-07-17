<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->foreignId('video_upload_type_id')->nullable()->after('video_url')->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->dropForeign(['video_upload_type_id']);
            $table->dropColumn('video_upload_type_id');
        });
    }
};