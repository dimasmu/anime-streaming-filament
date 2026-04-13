<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animes', function (Blueprint $table) {
            $table->string('japanese_title')->nullable()->after('title');
            $table->integer('release_year')->nullable()->after('release_date');
            $table->string('banner')->nullable()->after('cover_image');
            $table->boolean('is_adult')->default(false)->after('is_featured');
            $table->integer('sub_episodes')->nullable()->after('episodes_count');
            $table->integer('dub_episodes')->nullable()->after('sub_episodes');
            $table->enum('quality', ['HD', 'SD', '4K'])->nullable()->after('is_adult');
        });
    }

    public function down(): void
    {
        Schema::table('animes', function (Blueprint $table) {
            $table->dropColumn([
                'japanese_title',
                'release_year',
                'banner',
                'is_adult',
                'sub_episodes',
                'dub_episodes',
                'quality',
            ]);
        });
    }
};
