<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$totalAnime = App\Models\Anime::count();
$totalEpisodes = App\Models\Episode::count();
$latest = App\Models\Anime::latest('id')->first();

echo "═══════════════════════════════════════════\n";
echo "Database Summary\n";
echo "═══════════════════════════════════════════\n";
echo "Total anime:    {$totalAnime}\n";
echo "Total episodes: {$totalEpisodes}\n";
echo "Latest anime:   {$latest->title} (ID: {$latest->id})\n";
echo "═══════════════════════════════════════════\n";
