<?php

namespace Database\Seeders;

use App\Models\Anime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BulkOfflineAnimeSeeder extends Seeder
{
    public function run(): void
    {
        $count = (int) env('OFFLINE_BULK_ANIME_COUNT', 1000);
        $episodesPerAnime = (int) env('OFFLINE_BULK_EPISODES_COUNT', 12);

        $count = max(1, $count);
        $episodesPerAnime = max(1, $episodesPerAnime);

        $this->command?->info("BulkOfflineAnimeSeeder: count={$count}, episodes={$episodesPerAnime}");

        $createdAnime = 0;
        $createdEpisodes = 0;
        $skippedAnime = 0;

        for ($i = 1; $i <= $count; $i++) {
            $title = sprintf('Offline Seed Anime %04d', $i);
            $slug = 'offline-seed-anime-'.sprintf('%04d', $i);

            if (Anime::query()->where('slug', $slug)->exists()) {
                $skippedAnime++;
                continue;
            }

            $anime = Anime::create([
                'title' => $title,
                'slug' => $slug,

                // Fill all common columns with safe defaults.
                'description' => null,
                'synopsis' => null,
                'poster_image' => null,
                'cover_image' => null,
                'trailer_url' => null,
                'video_upload_type_id' => null,

                'status' => 'upcoming',
                'type' => 'tv',
                'episodes_count' => $episodesPerAnime,
                'duration' => null,
                'release_date' => null,
                'rating' => null,
                'views' => 0,
                'studio_id' => null,
                'source' => null,

                'is_featured' => false,
                'is_published' => true,

                'season_number' => null,
                'seasons_total' => null,
            ]);

            $createdAnime++;

            $rows = [];
            for ($ep = 1; $ep <= $episodesPerAnime; $ep++) {
                $rows[] = [
                    'anime_id' => $anime->id,
                    'episode_number' => $ep,
                    'title' => "Episode {$ep}",
                    'description' => null,
                    'thumbnail' => null,
                    'video_url' => null,
                    'video_upload_type_id' => null,
                    'duration' => null,
                    'air_date' => null,
                    'is_published' => true,
                    'likes' => 0,
                    'views' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('episodes')->upsert(
                $rows,
                ['anime_id', 'episode_number'],
                ['title', 'updated_at']
            );

            $createdEpisodes += $episodesPerAnime;

            if ($i % 100 === 0) {
                $this->command?->line("... {$i}/{$count}");
            }
        }

        $this->command?->info("BulkOfflineAnimeSeeder done: anime_created={$createdAnime}, anime_skipped={$skippedAnime}, episodes_upserted={$createdEpisodes}");
    }
}
