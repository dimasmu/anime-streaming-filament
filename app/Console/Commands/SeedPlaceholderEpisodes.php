<?php

namespace App\Console\Commands;

use App\Models\Anime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedPlaceholderEpisodes extends Command
{
    protected $signature = 'anime:seed-placeholders
        {--all : Generate placeholders for all anime}
        {--id=* : Anime IDs to generate placeholders for (can be passed multiple times)}
        {--count=12 : Number of placeholder episodes to create per anime}
        {--set-planned : If episodes_count is null, set it to --count}
        {--only-if-empty : Only seed if the anime currently has zero episodes}';

    protected $description = 'Create placeholder Episode rows (e.g., 12) for offline seeded anime without using external sources.';

    public function handle(): int
    {
        $all = (bool) $this->option('all');
        $ids = array_values(array_filter((array) $this->option('id')));
        $count = max(1, (int) $this->option('count'));
        $setPlanned = (bool) $this->option('set-planned');
        $onlyIfEmpty = (bool) $this->option('only-if-empty');

        if (! $all && count($ids) === 0) {
            $this->error('Provide --all or one/more --id= values.');
            return self::INVALID;
        }

        $query = Anime::query()->orderBy('id');
        if (! $all) {
            $query->whereIn('id', $ids);
        }

        $total = (clone $query)->count();
        $this->info("Seeding placeholders for {$total} anime(s) with {$count} episode(s) each...");

        $createdEpisodes = 0;
        $skipped = 0;

        $query->chunkById(50, function ($animes) use ($count, $setPlanned, $onlyIfEmpty, &$createdEpisodes, &$skipped) {
            foreach ($animes as $anime) {
                $existingCount = $anime->episodes()->count();
                if ($onlyIfEmpty && $existingCount > 0) {
                    $skipped++;
                    $this->line("[SKIP] #{$anime->id} {$anime->title} already has {$existingCount} episode(s)");
                    continue;
                }

                if ($setPlanned && $anime->episodes_count === null) {
                    $anime->episodes_count = $count;
                    $anime->save();
                }

                $rows = [];
                for ($num = 1; $num <= $count; $num++) {
                    $rows[] = [
                        'anime_id' => $anime->id,
                        'episode_number' => $num,
                        'title' => "Episode {$num}",
                        'description' => null,
                        'thumbnail' => null,
                        'video_url' => null,
                        'duration' => null,
                        'air_date' => null,
                        'is_published' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DB::table('episodes')->upsert(
                    $rows,
                    ['anime_id', 'episode_number'],
                    ['title', 'updated_at']
                );

                $createdEpisodes += $count;
                $this->line("[OK] #{$anime->id} {$anime->title} placeholders upserted");
            }
        });

        $this->info("Done. Episodes upserted={$createdEpisodes}, skipped={$skipped}");

        return self::SUCCESS;
    }
}
