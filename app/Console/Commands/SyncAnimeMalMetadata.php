<?php

namespace App\Console\Commands;

use App\Models\Anime;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncAnimeMalMetadata extends Command
{
    private array $detailsCache = [];
    private array $relationsCache = [];
    private array $franchiseCache = [];
    private array $franchiseSeriesCache = [];

    protected $signature = 'anime:sync-mal
        {--all : Sync all anime records}
        {--id=* : Anime IDs to sync (can be passed multiple times)}
        {--search : If mal_id is missing, search by title}
        {--force : Overwrite existing fields (episodes_count, synopsis, trailer_url, status, type)}
        {--seed-episodes : Create/update Episode rows from MAL episode list}
        {--seed-episodes-limit=50 : When using --seed-episodes, seed at most N episodes per anime (helps for long-running shows)}
        {--skip-franchise : Skip syncing season 1/2/3 franchise info via prequel/sequel relations}
        {--limit= : Max anime to sync (useful for testing)}';

    protected $description = 'Sync anime metadata (episodes, season/year, MAL id/url) using MyAnimeList data via the Jikan API.';

    public function handle(): int
    {
        $all = (bool) $this->option('all');
        $ids = array_values(array_filter(Arr::wrap($this->option('id'))));
        $shouldSearch = (bool) $this->option('search');
        $force = (bool) $this->option('force');
        $seedEpisodes = (bool) $this->option('seed-episodes');
        $seedEpisodesLimit = (int) $this->option('seed-episodes-limit');
        if ($seedEpisodesLimit < 0) {
            $seedEpisodesLimit = 0;
        }
        $skipFranchise = (bool) $this->option('skip-franchise');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        if (! $all && count($ids) === 0) {
            $this->error('Provide --all or one/more --id= values.');
            return self::INVALID;
        }

        $query = Anime::query()->orderBy('id');

        if (! $all) {
            $query->whereIn('id', $ids);
        }

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $total = (clone $query)->count();
        $this->info("Syncing {$total} anime(s)...");

        $synced = 0;
        $failed = 0;

        $query->chunkById(25, function ($animes) use (&$synced, &$failed, $shouldSearch, $force, $seedEpisodes, $seedEpisodesLimit, $skipFranchise) {
            foreach ($animes as $anime) {
                try {
                    $result = $this->syncOne(
                        $anime,
                        shouldSearch: $shouldSearch,
                        force: $force,
                        seedEpisodes: $seedEpisodes,
                        seedEpisodesLimit: $seedEpisodesLimit,
                        skipFranchise: $skipFranchise,
                    );
                    $synced++;
                    $seasonInfo = '';
                    if (Arr::get($result, 'mal_season_number') !== null && Arr::get($result, 'mal_seasons_total') !== null) {
                        $seasonInfo = ' (Season '.Arr::get($result, 'mal_season_number').' of '.Arr::get($result, 'mal_seasons_total').')';
                    }
                    $this->line("[OK] #{$anime->id} {$anime->title}{$seasonInfo}");
                } catch (\Throwable $e) {
                    $failed++;
                    $this->warn("[FAIL] #{$anime->id} {$anime->title}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Done. Synced={$synced}, Failed={$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function syncOne(Anime $anime, bool $shouldSearch, bool $force, bool $seedEpisodes, int $seedEpisodesLimit, bool $skipFranchise): array
    {
        $malId = $anime->mal_id;

        if (! $malId && $shouldSearch) {
            $searchResult = $this->jikanSearchAnimeByTitle($anime->title);
            $malId = $searchResult['mal_id'] ?? null;

            if ($malId) {
                $anime->mal_id = $malId;
                $anime->mal_url = $searchResult['url'] ?? null;
            }
        }

        if (! $malId) {
            throw new \RuntimeException('mal_id is missing (use --search or set mal_id manually).');
        }

        $details = $this->jikanGetAnimeDetails($malId);

        $malUrl = Arr::get($details, 'url');
        $season = Arr::get($details, 'season');
        $year = Arr::get($details, 'year');
        $episodes = Arr::get($details, 'episodes');

        $anime->mal_url = $anime->mal_url ?? $malUrl;
        $anime->mal_season = $season ? strtolower((string) $season) : $anime->mal_season;
        $anime->mal_year = $year ? (int) $year : $anime->mal_year;
        $anime->mal_episodes_count = $episodes !== null ? (int) $episodes : $anime->mal_episodes_count;

        if ($force || $anime->episodes_count === null) {
            if ($episodes !== null) {
                $anime->episodes_count = (int) $episodes;
            }
        }

        $synopsis = Arr::get($details, 'synopsis');
        if (($force || $anime->synopsis === null) && is_string($synopsis) && $synopsis !== '') {
            $anime->synopsis = $synopsis;
        }

        $trailerUrl = Arr::get($details, 'trailer.url');
        if (($force || $anime->trailer_url === null) && is_string($trailerUrl) && $trailerUrl !== '') {
            $anime->trailer_url = $trailerUrl;
        }

        $status = Arr::get($details, 'status');
        if ($force || $anime->status === 'upcoming') {
            $mapped = $this->mapMalStatusToLocal($status);
            if ($mapped) {
                $anime->status = $mapped;
            }
        }

        $type = Arr::get($details, 'type');
        if ($force || $anime->type === 'tv') {
            $mappedType = $this->mapMalTypeToLocal($type);
            if ($mappedType) {
                $anime->type = $mappedType;
            }
        }

        $anime->mal_last_synced_at = now();

        $score = Arr::get($details, 'score');
        if (($force || $anime->rating === null) && $score !== null) {
            $anime->rating = round((float) $score, 1);
        }

        $source = Arr::get($details, 'source');
        if (($force || $anime->source === null) && is_string($source) && $source !== '') {
            $anime->source = Str::lower($source);
        }

        $airedFrom = Arr::get($details, 'aired.from');
        if (($force || $anime->release_date === null) && is_string($airedFrom) && $airedFrom !== '') {
            try {
                $anime->release_date = Carbon::parse($airedFrom)->toDateString();
            } catch (\Throwable) {
                // ignore parse failures
            }
        }

        $durationText = Arr::get($details, 'duration');
        if (($force || $anime->duration === null) && is_string($durationText) && $durationText !== '') {
            if (preg_match('/(\d+)\s*min/i', $durationText, $matches) === 1) {
                $anime->duration = (int) $matches[1];
            }
        }

        $anime->save();

        if (! $skipFranchise) {
            $this->syncFranchiseSeasonInfo($anime, (int) $malId);
        }

        if ($seedEpisodes) {
            $this->seedEpisodesFromMal($anime, (int) $malId, $seedEpisodesLimit);
        }

        return [
            'mal_season_number' => $anime->mal_season_number,
            'mal_seasons_total' => $anime->mal_seasons_total,
        ];
    }

    private function syncFranchiseSeasonInfo(Anime $anime, int $malId): void
    {
        $franchise = $this->getFranchiseSeasonData($malId);

        $anime->mal_franchise_root_id = Arr::get($franchise, 'root_id');
        $anime->mal_season_number = Arr::get($franchise, 'season_number');
        $anime->mal_seasons_total = Arr::get($franchise, 'seasons_total');
        $anime->mal_franchise_last_synced_at = now();
        $anime->save();
    }

    private function getFranchiseSeasonData(int $malId): array
    {
        if (array_key_exists($malId, $this->franchiseCache)) {
            return $this->franchiseCache[$malId];
        }

        $rootId = $this->findFranchiseRootByPrequels($malId);

        if (! array_key_exists($rootId, $this->franchiseSeriesCache)) {
            $chain = $this->buildLinearSequelChain($rootId);

            $seasonIds = [];
            foreach ($chain as $id) {
                $details = $this->jikanGetAnimeDetails((int) $id);
                $malType = Arr::get($details, 'type');
                $mapped = $this->mapMalTypeToLocal(is_string($malType) ? $malType : null);

                // Treat TV/ONA as "seasons"; exclude movies/OVAs/specials.
                if (in_array($mapped, ['tv', 'ona'], true)) {
                    $seasonIds[] = (int) $id;
                }
            }

            $rootSeasonId = count($seasonIds) > 0 ? $seasonIds[0] : $rootId;

            $this->franchiseSeriesCache[$rootId] = [
                'root_id' => $rootSeasonId,
                'season_ids' => $seasonIds,
                'chain' => $chain,
            ];
        }

        $series = $this->franchiseSeriesCache[$rootId];
        $seasonIds = Arr::get($series, 'season_ids', []);

        $seasonNumber = null;
        $pos = array_search($malId, $seasonIds, true);
        if ($pos !== false) {
            $seasonNumber = $pos + 1;
        }

        $payload = [
            'root_id' => Arr::get($series, 'root_id'),
            'season_number' => $seasonNumber,
            'seasons_total' => is_array($seasonIds) ? count($seasonIds) : 0,
        ];

        $this->franchiseCache[$malId] = $payload;
        return $payload;
    }

    private function findFranchiseRootByPrequels(int $malId): int
    {
        $current = $malId;
        $visited = [];

        while (true) {
            if (isset($visited[$current])) {
                break;
            }
            $visited[$current] = true;

            $relations = $this->jikanGetAnimeRelations($current);
            $prequels = $this->extractRelatedAnimeIds($relations, relationName: 'Prequel');

            if (count($prequels) === 0) {
                break;
            }

            $next = $this->pickPreferredNextId($prequels);
            if ($next === null) {
                break;
            }

            $current = $next;
        }

        return $current;
    }

    private function buildLinearSequelChain(int $rootId): array
    {
        $chain = [$rootId];
        $current = $rootId;
        $visited = [$rootId => true];

        while (true) {
            $relations = $this->jikanGetAnimeRelations($current);
            $sequels = $this->extractRelatedAnimeIds($relations, relationName: 'Sequel');
            $sequels = array_values(array_filter($sequels, fn ($id) => ! isset($visited[$id])));

            if (count($sequels) === 0) {
                break;
            }

            $next = $this->pickPreferredNextId($sequels);
            if ($next === null) {
                break;
            }

            $visited[$next] = true;
            $chain[] = $next;
            $current = $next;

            // Safety guard to prevent infinite loops from bad data.
            if (count($chain) > 50) {
                break;
            }
        }

        return $chain;
    }

    private function pickPreferredNextId(array $candidateIds): ?int
    {
        $candidateIds = array_values(array_unique(array_map('intval', $candidateIds)));
        if (count($candidateIds) === 0) {
            return null;
        }

        if (count($candidateIds) === 1) {
            return (int) $candidateIds[0];
        }

        // Prefer TV/ONA when multiple choices exist.
        foreach ($candidateIds as $id) {
            try {
                $details = $this->jikanGetAnimeDetails((int) $id);
                $malType = Arr::get($details, 'type');
                $mapped = $this->mapMalTypeToLocal(is_string($malType) ? $malType : null);
                if (in_array($mapped, ['tv', 'ona'], true)) {
                    return (int) $id;
                }
            } catch (\Throwable) {
                // ignore and continue
            }
        }

        return (int) $candidateIds[0];
    }

    private function extractRelatedAnimeIds(array $relationsPayload, string $relationName): array
    {
        $out = [];
        $relations = Arr::get($relationsPayload, 'data', []);
        if (! is_array($relations)) {
            return [];
        }

        $relationName = strtolower(trim($relationName));

        foreach ($relations as $relation) {
            $name = Arr::get($relation, 'relation');
            if (! is_string($name) || strtolower(trim($name)) !== $relationName) {
                continue;
            }

            $entries = Arr::get($relation, 'entry', []);
            if (! is_array($entries)) {
                continue;
            }

            foreach ($entries as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $type = Arr::get($entry, 'type');
                if (! is_string($type) || strtolower($type) !== 'anime') {
                    continue;
                }

                $id = Arr::get($entry, 'mal_id');
                if (is_int($id) || (is_string($id) && ctype_digit($id))) {
                    $out[] = (int) $id;
                }
            }
        }

        return array_values(array_unique($out));
    }

    private function seedEpisodesFromMal(Anime $anime, int $malId, int $limit): void
    {
        if ($limit === 0) {
            return;
        }

        $page = 1;
        $rows = [];
        $seeded = 0;

        while (true) {
            $payload = $this->jikanGetAnimeEpisodesPage($malId, $page);
            $data = Arr::get($payload, 'data', []);
            $pagination = Arr::get($payload, 'pagination', []);

            foreach ($data as $ep) {
                if ($limit > 0 && $seeded >= $limit) {
                    break 2;
                }

                $number = (int) Arr::get($ep, 'mal_id', 0);
                $title = (string) (Arr::get($ep, 'title') ?? '');

                // Jikan episode list uses 'mal_id' as episode number (1..N)
                if ($number <= 0) {
                    continue;
                }

                $aired = Arr::get($ep, 'aired');
                $airDate = null;
                if (is_string($aired) && $aired !== '') {
                    try {
                        $airDate = Carbon::parse($aired)->toDateString();
                    } catch (\Throwable) {
                        $airDate = null;
                    }
                }

                $rows[] = [
                    'anime_id' => $anime->id,
                    'episode_number' => $number,
                    'title' => $title !== '' ? $title : "Episode {$number}",
                    'air_date' => $airDate,
                    'is_published' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $seeded++;
            }

            $hasNext = (bool) Arr::get($pagination, 'has_next_page', false);
            if (! $hasNext) {
                break;
            }

            $page++;

            // keep memory bounded
            if (count($rows) >= 1000) {
                $this->upsertEpisodeRows($rows);
                $rows = [];
            }
        }

        if (count($rows) > 0) {
            $this->upsertEpisodeRows($rows);
        }
    }

    private function upsertEpisodeRows(array $rows): void
    {
        DB::table('episodes')->upsert(
            $rows,
            ['anime_id', 'episode_number'],
            ['title', 'air_date', 'updated_at']
        );
    }

    private function jikanSearchAnimeByTitle(string $title): array
    {
        $query = Str::of($title)->squish()->toString();

        /** @var Response $response */
        $response = Http::retry(3, 500)
            ->timeout(20)
            ->get('https://api.jikan.moe/v4/anime', [
                'q' => $query,
                'limit' => 5,
                'order_by' => 'members',
                'sort' => 'desc',
            ]);

        $json = $response->throw()->json();
        $data = Arr::get($json, 'data', []);
        if (! is_array($data) || count($data) === 0) {
            return [];
        }

        $best = null;
        $bestScore = -1.0;
        $normalizedQuery = $this->normalizeTitleForMatch($query);

        foreach ($data as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }

            $titles = array_filter([
                Arr::get($candidate, 'title'),
                Arr::get($candidate, 'title_english'),
                Arr::get($candidate, 'title_japanese'),
            ], fn ($t) => is_string($t) && $t !== '');

            $candidateBest = 0.0;
            foreach ($titles as $t) {
                $normalizedCandidate = $this->normalizeTitleForMatch($t);
                if ($normalizedCandidate === '' || $normalizedQuery === '') {
                    continue;
                }

                similar_text($normalizedQuery, $normalizedCandidate, $percent);
                $candidateBest = max($candidateBest, (float) $percent);
            }

            // Light bonus if it looks like TV (helps avoid picking movies/specials).
            $type = Arr::get($candidate, 'type');
            if (is_string($type) && strtolower($type) === 'tv') {
                $candidateBest += 2.0;
            }

            if ($candidateBest > $bestScore) {
                $bestScore = $candidateBest;
                $best = $candidate;
            }
        }

        // Prevent obviously wrong matches.
        if ($bestScore < 35.0) {
            return [];
        }

        return is_array($best) ? $best : [];
    }

    private function normalizeTitleForMatch(string $value): string
    {
        $value = Str::lower($value);
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value) ?? '';
        return Str::of($value)->squish()->toString();
    }

    private function jikanGetAnimeDetails(int $malId): array
    {
        if (array_key_exists($malId, $this->detailsCache)) {
            return $this->detailsCache[$malId];
        }

        /** @var Response $response */
        $response = Http::retry(3, 700)
            ->timeout(20)
            ->get("https://api.jikan.moe/v4/anime/{$malId}");

        $json = $response->throw()->json();
        $data = Arr::get($json, 'data');

        if (! is_array($data)) {
            throw new \RuntimeException('Jikan details returned unexpected payload.');
        }

        $this->detailsCache[$malId] = $data;
        return $data;
    }

    private function jikanGetAnimeRelations(int $malId): array
    {
        if (array_key_exists($malId, $this->relationsCache)) {
            return $this->relationsCache[$malId];
        }

        /** @var Response $response */
        $response = Http::retry(3, 700)
            ->timeout(20)
            ->get("https://api.jikan.moe/v4/anime/{$malId}/relations");

        $json = $response->throw()->json();
        $payload = is_array($json) ? $json : [];
        $this->relationsCache[$malId] = $payload;

        return $payload;
    }

    private function jikanGetAnimeEpisodesPage(int $malId, int $page): array
    {
        /** @var Response $response */
        $response = Http::retry(3, 700)
            ->timeout(30)
            ->get("https://api.jikan.moe/v4/anime/{$malId}/episodes", [
                'page' => $page,
            ]);

        $json = $response->throw()->json();

        return is_array($json) ? $json : [];
    }

    private function mapMalStatusToLocal(?string $status): ?string
    {
        $status = strtolower((string) $status);

        return match (true) {
            Str::contains($status, 'currently airing') => 'ongoing',
            Str::contains($status, 'finished airing') => 'completed',
            Str::contains($status, 'not yet aired') => 'upcoming',
            default => null,
        };
    }

    private function mapMalTypeToLocal(?string $type): ?string
    {
        $type = strtolower((string) $type);

        return match (true) {
            $type === 'tv' => 'tv',
            $type === 'movie' => 'movie',
            $type === 'ova' => 'ova',
            $type === 'ona' => 'ona',
            $type === 'special' => 'special',
            default => null,
        };
    }
}
