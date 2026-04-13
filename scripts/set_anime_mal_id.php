<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$animeId = (int) ($argv[1] ?? 0);
$malId = (int) ($argv[2] ?? 0);

if ($animeId <= 0 || $malId <= 0) {
    fwrite(STDERR, "Usage: php scripts/set_anime_mal_id.php <anime_id> <mal_id>\n");
    exit(1);
}

$anime = App\Models\Anime::find($animeId);
if (! $anime) {
    fwrite(STDERR, "Anime not found: {$animeId}\n");
    exit(1);
}

$anime->mal_id = $malId;
$anime->mal_url = "https://myanimelist.net/anime/{$malId}";
$anime->save();

echo "Updated anime #{$anime->id} ({$anime->title}) to mal_id={$malId}\n";
