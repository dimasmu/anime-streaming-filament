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
        Schema::table('animes', function (Blueprint $table) {
            // Drop MAL-specific columns
            if (Schema::hasColumn('animes', 'mal_id')) {
                $table->dropUnique(['mal_id']);
            }

            if (Schema::hasColumn('animes', 'mal_franchise_root_id')) {
                $table->dropIndex(['mal_franchise_root_id', 'mal_season_number']);
            }

            $columnsToRemove = [
                'mal_id',
                'mal_url',
                'mal_season',
                'mal_year',
                'mal_episodes_count',
                'mal_last_synced_at',
                'mal_franchise_root_id',
                'mal_franchise_last_synced_at',
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('animes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Rename mal_season_number to season_number and mal_seasons_total to seasons_total
        Schema::table('animes', function (Blueprint $table) {
            if (Schema::hasColumn('animes', 'mal_season_number')) {
                $table->renameColumn('mal_season_number', 'season_number');
            }

            if (Schema::hasColumn('animes', 'mal_seasons_total')) {
                $table->renameColumn('mal_seasons_total', 'seasons_total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename columns back
        Schema::table('animes', function (Blueprint $table) {
            if (Schema::hasColumn('animes', 'season_number')) {
                $table->renameColumn('season_number', 'mal_season_number');
            }

            if (Schema::hasColumn('animes', 'seasons_total')) {
                $table->renameColumn('seasons_total', 'mal_seasons_total');
            }
        });

        // Restore MAL columns
        Schema::table('animes', function (Blueprint $table) {
            $table->unsignedBigInteger('mal_id')->nullable()->unique()->after('slug');
            $table->string('mal_url')->nullable()->after('mal_id');
            $table->enum('mal_season', ['winter', 'spring', 'summer', 'fall'])->nullable()->after('mal_url');
            $table->unsignedSmallInteger('mal_year')->nullable()->after('mal_season');
            $table->unsignedSmallInteger('mal_episodes_count')->nullable()->after('mal_year');
            $table->timestamp('mal_last_synced_at')->nullable()->after('mal_episodes_count');
            $table->unsignedBigInteger('mal_franchise_root_id')->nullable()->after('mal_last_synced_at');
            $table->timestamp('mal_franchise_last_synced_at')->nullable()->after('seasons_total');

            $table->index(['mal_franchise_root_id', 'mal_season_number']);
        });
    }
};
