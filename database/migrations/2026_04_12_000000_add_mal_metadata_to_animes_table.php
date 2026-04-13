<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animes', function (Blueprint $table) {
            $table->unsignedBigInteger('mal_id')->nullable()->unique()->after('slug');
            $table->string('mal_url')->nullable()->after('mal_id');
            $table->enum('mal_season', ['winter', 'spring', 'summer', 'fall'])->nullable()->after('mal_url');
            $table->unsignedSmallInteger('mal_year')->nullable()->after('mal_season');
            $table->unsignedSmallInteger('mal_episodes_count')->nullable()->after('mal_year');
            $table->timestamp('mal_last_synced_at')->nullable()->after('mal_episodes_count');
        });

        if (app()->environment('local') && (bool) env('SELF_DELETE_ANIME_MAL_MIGRATION', false)) {
            @unlink(__FILE__);
        }
    }

    public function down(): void
    {
        Schema::table('animes', function (Blueprint $table) {
            $table->dropUnique(['mal_id']);
            $table->dropColumn([
                'mal_id',
                'mal_url',
                'mal_season',
                'mal_year',
                'mal_episodes_count',
                'mal_last_synced_at',
            ]);
        });
    }
};
