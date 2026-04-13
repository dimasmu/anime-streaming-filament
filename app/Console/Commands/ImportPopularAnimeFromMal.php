<?php

namespace App\Console\Commands;

use App\Models\Anime;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportPopularAnimeFromMal extends Command
{
    protected $signature = 'anime:import-mal-popular
        {--target=50 : Number of new anime to import}
        {--from=2010-01-01 : Only include anime airing/released on or after this date (YYYY-MM-DD)}
        {--min-score=7.5 : Only include anime with score >= this}
        {--allowed-episodes=12,24 : Comma-separated list of allowed episode counts (preferred)}
        {--max-episodes=24 : Skip anime with more than this many episodes (avoid long series)}
        {--include-mal-id=* : MAL IDs to always include (e.g., SAO S1=11757, Angel Beats=6547)}
        {--sync-existing : Also sync anime already in your DB (matched by mal_id)}
        {--no-seed-episodes : Do not seed Episode rows}
        {--seed-episodes-limit=50 : When seeding episodes, seed at most N per anime}
        {--dry-run : Show what would be imported but do not write to DB}';

    protected $description = 'Import popular, high-rated TV anime from MyAnimeList (via Jikan), prioritizing 12/24-episode seasons and avoiding long series.';

    public function handle(): int
    {
        $target = max(1, (int) $this->option('target'));
        $from = (string) $this->option('from');
        $minScore = (float) $this->option('min-score');
        $maxEpisodes = (int) $this->option('max-episodes');
        $allowedEpisodes = $this->parseAllowedEpisodes((string) $this->option('allowed-episodes'));
        $includeMalIds = array_values(array_unique(array_map('intval', Arr::wrap($this->option('include-mal-id')))));
        $syncExisting = (bool) $this->option('sync-existing');
        $seedEpisodes = ! (bool) $this->option('no-seed-episodes');
        $seedEpisodesLimit = max(0, (int) $this->option('seed-episodes-limit'));
        $dryRun = (bool) $this->option('dry-run');

        if (count($includeMalIds) === 0) {
            $includeMalIds = [11757, 6547];
        }

        $this->info('Import settings:');
        $this->line("- target new anime: {$target}");
        $this->line("- from: {$from}");
        $this->line("- min score: {$minScore}");
        $this->line("- allowed episodes: ".(count($allowedEpisodes) ? implode(',', $allowedEpisodes) : '(any)'));
        $this->line("- max episodes: {$maxEpisodes}");
        $this->line("- seed episodes: ".($seedEpisodes ? 'yes' : 'no')." (limit {$seedEpisodesLimit})");
        $this->line("- dry run: ".($dryRun ? 'yes' : 'no'));

        $imported = 0;
        $considered = 0;

        // 1) Always include specific MAL IDs first (e.g., SAO S1, Angel Beats)
        foreach ($includeMalIds as $malId) {
            $considered++;
            $result = $this->importOneMalId($malId, $dryRun, $seedEpisodes, $seedEpisodesLimit, $syncExisting);
            if ($result === 'imported') {
                $imported++;
                if ($imported >= $target) {
                    $this->info("Done. Imported {$imported} new anime.");
                    return self::SUCCESS;
                }
            }
        }

        // 2) Pull popular TV anime from Jikan search and filter to short seasons
        $page = 1;
        $limit = 25;

        while ($imported < $target) {
            $payload = $this->jikanBrowsePopularTv(page: $page, limit: $limit, from: $from, minScore: $minScore);
            if ($payload === null) {
                $this->warn('Jikan browse failed temporarily (will stop this run). Try again in a minute.');
                break;
            }
            $data = Arr::get($payload, 'data', []);
            $pagination = Arr::get($payload, 'pagination', []);

            if (! is_array($data) || count($data) === 0) {
                $this->warn('No more results from Jikan.');
                break;
            }

            foreach ($data as $row) {
                $considered++;

                $malId = (int) Arr::get($row, 'mal_id', 0);
                if ($malId <= 0) {
                    continue;
                }

                // Prefer short seasons.
                $episodes = Arr::get($row, 'episodes');
                if (! is_int($episodes)) {
                    $episodes = is_numeric($episodes) ? (int) $episodes : null;
                }

                if ($episodes === null) {
                    continue;
                }

                if ($maxEpisodes > 0 && $episodes > $maxEpisodes) {
                    continue;
                }

                if (count($allowedEpisodes) > 0 && ! in_array($episodes, $allowedEpisodes, true)) {
                    continue;
                }

                $score = Arr::get($row, 'score');
                if ($score !== null && (float) $score < $minScore) {
                    continue;
                }

                $result = $this->importOneFromBrowseRow($row, $dryRun, $seedEpisodes, $seedEpisodesLimit, $syncExisting);
                if ($result === 'imported') {
                    $imported++;
                    if ($imported >= $target) {
                        break;
                    }
                }
            }

            $hasNext = (bool) Arr::get($pagination, 'has_next_page', false);
            if (! $hasNext) {
                break;
            }

            $page++;
        }

        $this->info("Done. Imported {$imported} new anime. Considered {$considered}.");

        return self::SUCCESS;
    }

    private function importOneFromBrowseRow(array $row, bool $dryRun, bool $seedEpisodes, int $seedEpisodesLimit, bool $syncExisting): string
    {
        $malId = (int) Arr::get($row, 'mal_id', 0);
        $title = (string) (Arr::get($row, 'title') ?? '');
        $url = (string) (Arr::get($row, 'url') ?? '');

        if ($malId <= 0 || $title === '') {
            return 'skipped';
        }

        return $this->importOrSyncAnime($malId, $title, $url, $dryRun, $seedEpisodes, $seedEpisodesLimit, $syncExisting);
    }

    private function importOneMalId(int $malId, bool $dryRun, bool $seedEpisodes, int $seedEpisodesLimit, bool $syncExisting): string
    {
        if ($malId <= 0) {
            return 'skipped';
        }

        $details = $this->jikanGetAnimeDetails($malId);
        $title = (string) (Arr::get($details, 'title') ?? '');
        $url = (string) (Arr::get($details, 'url') ?? '');

        if ($title === '') {
            $title = 'MAL '.$malId;
        }

        return $this->importOrSyncAnime($malId, $title, $url, $dryRun, $seedEpisodes, $seedEpisodesLimit, $syncExisting);
    }

    private function importOrSyncAnime(int $malId, string $title, string $malUrl, bool $dryRun, bool $seedEpisodes, int $seedEpisodesLimit, bool $syncExisting): string
    {
        $existing = Anime::query()->where('mal_id', $malId)->first();

        if ($existing) {
            if (! $syncExisting) {
                $this->line("[SKIP] Exists: {$existing->title} (MAL {$malId})");
                return 'skipped';
            }

            if ($dryRun) {
                $this->line("[DRY] Would sync existing: {$existing->title} (MAL {$malId})");
                return 'skipped';
            }

            $this->syncAnimeById($existing->id, $seedEpisodes, $seedEpisodesLimit);
            $this->line("[SYNC] {$existing->title} (MAL {$malId})");
            return 'synced';
        }

        $slug = Str::slug($title);
        if ($slug === '') {
            $slug = 'mal-'.$malId;
        }
        $slug = $slug.'-'.$malId;

        if ($dryRun) {
            $this->line("[DRY] Would import: {$title} (MAL {$malId})");
            return 'imported';
        }

        $anime = Anime::create([
            'title' => $title,
            'slug' => $slug,
            'mal_id' => $malId,
            'mal_url' => $malUrl !== '' ? $malUrl : null,
            'status' => 'upcoming',
            'type' => 'tv',
            'is_featured' => false,
            'is_published' => false,
        ]);

        $this->syncAnimeById($anime->id, $seedEpisodes, $seedEpisodesLimit);
        $this->line("[ADD] {$anime->title} (MAL {$malId})");

        return 'imported';
    }

    private function syncAnimeById(int $animeId, bool $seedEpisodes, int $seedEpisodesLimit): void
    {
        $args = [
            '--id' => [$animeId],
            '--force' => true,
        ];

        if ($seedEpisodes) {
            $args['--seed-episodes'] = true;
            $args['--seed-episodes-limit'] = $seedEpisodesLimit;
        }

        Artisan::call('anime:sync-mal', $args, $this->output);

        // Small pause to be polite to Jikan rate limits.
        usleep(300000);
    }

    private function parseAllowedEpisodes(string $csv): array
    {
        $parts = array_filter(array_map('trim', explode(',', $csv)));
        $nums = [];
        foreach ($parts as $p) {
            if ($p === '') {
                continue;
            }
            if (! ctype_digit($p)) {
                continue;
            }
            $nums[] = (int) $p;
        }

        $nums = array_values(array_unique($nums));
        sort($nums);

        return $nums;
    }

    private function jikanBrowsePopularTv(int $page, int $limit, string $from, float $minScore): ?array
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            try {
                /** @var Response $response */
                $response = Http::retry(2, 1000)
                    ->timeout(30)
                    ->get('https://api.jikan.moe/v4/anime', [
                        'type' => 'tv',
                        'order_by' => 'members',
                        'sort' => 'desc',
                        'min_score' => $minScore,
                        'start_date' => $from,
                        'page' => $page,
                        'limit' => $limit,
                    ]);

                $json = $response->throw()->json();
                return is_array($json) ? $json : [];
            } catch (\Throwable $e) {
                $this->warn("Jikan browse error (attempt {$attempt}/5): {$e->getMessage()}");

                // Fallback: Jikan top list sometimes works even when the search endpoint is flaky.
                $fallback = $this->jikanTopTv(page: $page, limit: $limit);
                if ($fallback !== null) {
                    $this->warn('Using Jikan top list fallback for this page.');
                    return $fallback;
                }

                usleep(900000);
            }
        }

        return null;
    }

    private function jikanTopTv(int $page, int $limit): ?array
    {
        try {
            /** @var Response $response */
            $response = Http::retry(2, 1200)
                ->timeout(30)
                ->get('https://api.jikan.moe/v4/top/anime', [
                    'type' => 'tv',
                    'page' => $page,
                    'limit' => $limit,
                ]);

            $json = $response->throw()->json();
            return is_array($json) ? $json : [];
        } catch (\Throwable) {
            return null;
        }
    }

    private function jikanGetAnimeDetails(int $malId): array
    {
        try {
            /** @var Response $response */
            $response = Http::retry(3, 800)
                ->timeout(30)
                ->get("https://api.jikan.moe/v4/anime/{$malId}");

            $json = $response->throw()->json();
            $data = Arr::get($json, 'data');

            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            $this->warn("Jikan details error for MAL {$malId}: {$e->getMessage()}");
            return [];
        }
    }
}
