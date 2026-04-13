<?php

namespace Database\Seeders;

use App\Models\Anime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CuratedOfflineAnimeSeeder extends Seeder
{
    public function run(): void
    {
        $defaultEpisodes = (int) env('OFFLINE_CURATED_EPISODES_COUNT', 12);
        $defaultEpisodes = max(1, $defaultEpisodes);

        $path = database_path('data/curated_anime.json');

        if (! File::exists($path)) {
            $this->command?->warn("Curated list not found: {$path}");
            return;
        }

        $json = File::get($path);
        $items = json_decode($json, true);

        if (! is_array($items)) {
            $this->command?->warn('Curated list JSON is invalid.');
            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = $item['title'] ?? null;
            if (! is_string($title) || trim($title) === '') {
                continue;
            }

            $title = Str::of($title)->squish()->toString();
            $baseSlug = Str::slug($title);
            if ($baseSlug === '') {
                $baseSlug = 'anime';
            }

            // Make slug deterministic and unique per title.
            $slug = $baseSlug.'-'.substr(md5($title), 0, 6);

            $existing = Anime::query()->where('slug', $slug)->first();
            if ($existing) {
                $skipped++;
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

                // Offline seed: do not guess episodes/dates/ratings.
                'type' => 'tv',
                'status' => 'upcoming',
                'episodes_count' => $defaultEpisodes,
                'duration' => null,
                'release_date' => null,
                'rating' => null,
                'views' => 0,
                'studio_id' => null,
                'source' => null,
                'is_featured' => false,
                'is_published' => true,

                // Season fields (optional)
                'season_number' => null,
                'seasons_total' => null,
            ]);

            // Also seed placeholder episodes (offline scaffolding).
            $rows = [];
            for ($num = 1; $num <= $defaultEpisodes; $num++) {
                $rows[] = [
                    'anime_id' => $anime->id,
                    'episode_number' => $num,
                    'title' => "Episode {$num}",
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

            $created++;
        }

        $this->command?->info("CuratedOfflineAnimeSeeder: created={$created}, skipped={$skipped}");
    }
}
