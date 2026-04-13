<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animes', function (Blueprint $table) {
            $table->unsignedBigInteger('mal_franchise_root_id')->nullable()->after('mal_last_synced_at');
            $table->unsignedSmallInteger('mal_season_number')->nullable()->after('mal_franchise_root_id');
            $table->unsignedSmallInteger('mal_seasons_total')->nullable()->after('mal_season_number');
            $table->timestamp('mal_franchise_last_synced_at')->nullable()->after('mal_seasons_total');

            $table->index(['mal_franchise_root_id', 'mal_season_number']);
        });

        if (app()->environment('local') && (bool) env('SELF_DELETE_ANIME_MAL_MIGRATION', false)) {
            @unlink(__FILE__);
        }
    }

    public function down(): void
    {
        Schema::table('animes', function (Blueprint $table) {
            $table->dropIndex(['mal_franchise_root_id', 'mal_season_number']);
            $table->dropColumn([
                'mal_franchise_root_id',
                'mal_season_number',
                'mal_seasons_total',
                'mal_franchise_last_synced_at',
            ]);
        });
    }
};
