<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$slug = $argv[1] ?? null;
if (! is_string($slug) || $slug === '') {
    fwrite(STDERR, "Usage: php scripts/inspect_one_anime.php <slug>\n");
    exit(1);
}

$anime = App\Models\Anime::query()->where('slug', $slug)->first();
if (! $anime) {
    fwrite(STDERR, "Not found: {$slug}\n");
    exit(1);
}

$data = $anime->only([
    'id',
    'title',
    'slug',
    'status',
    'type',
    'episodes_count',
    'views',
    'rating',
    'release_date',
    'source',
    'is_featured',
    'is_published',
    'mal_id',
    'mal_season',
    'mal_year',
    'mal_season_number',
    'mal_seasons_total',
]);

$data['episodes_rows'] = $anime->episodes()->count();

echo json_encode($data, JSON_PRETTY_PRINT)."\n";
