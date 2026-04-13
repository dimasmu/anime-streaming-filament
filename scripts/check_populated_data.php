<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Anime;
use App\Models\Episode;

echo "Checking populated data...\n";
echo str_repeat('-', 50) . "\n";

// Anime statistics
echo "\n=== ANIME STATISTICS ===\n";

$statusCounts = Anime::selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->get();

echo "\nStatus breakdown:\n";
foreach ($statusCounts as $stat) {
    echo "  {$stat->status}: {$stat->count}\n";
}

$avgRating = Anime::avg('rating');
$minRating = Anime::min('rating');
$maxRating = Anime::max('rating');
echo "\nRatings:\n";
echo "  Average: " . number_format($avgRating, 2) . "\n";
echo "  Min: " . number_format($minRating, 1) . "\n";
echo "  Max: " . number_format($maxRating, 1) . "\n";

$withRating = Anime::whereNotNull('rating')->count();
echo "  Anime with ratings: {$withRating}/1071\n";

$withDate = Anime::whereNotNull('release_date')->count();
echo "\nAnime with release dates: {$withDate}/1071\n";

$withSource = Anime::whereNotNull('source')->count();
echo "Anime with source: {$withSource}/1071\n";

// Episodes statistics
echo "\n=== EPISODE STATISTICS ===\n";

$totalLikes = Episode::sum('likes');
$avgLikes = Episode::avg('likes');
$minLikes = Episode::min('likes');
$maxLikes = Episode::max('likes');

echo "\nEpisode Likes:\n";
echo "  Total: " . number_format($totalLikes) . "\n";
echo "  Average: " . number_format($avgLikes, 0) . " per episode\n";
echo "  Min: " . number_format($minLikes) . "\n";
echo "  Max: " . number_format($maxLikes) . "\n";

$withDuration = Episode::whereNotNull('duration')->count();
echo "\nEpisodes with duration: {$withDuration}/12705\n";

// Sample data
echo "\n=== SAMPLE DATA ===\n";

$sampleAnime = Anime::with('episodes')->inRandomOrder()->first();
echo "\nRandom anime: {$sampleAnime->title}\n";
echo "  Status: {$sampleAnime->status}\n";
echo "  Rating: " . ($sampleAnime->rating ? number_format($sampleAnime->rating, 1) : 'N/A') . "\n";
echo "  Episodes: {$sampleAnime->episodes_count}\n";
echo "  Duration: " . ($sampleAnime->duration ? $sampleAnime->duration . ' min' : 'N/A') . "\n";
echo "  Release date: " . ($sampleAnime->release_date ?: 'N/A') . "\n";
echo "  Source: " . ($sampleAnime->source ?: 'N/A') . "\n";

if ($sampleAnime->episodes->isNotEmpty()) {
    $sampleEpisode = $sampleAnime->episodes->random();
    echo "\n  Sample episode {$sampleEpisode->episode_number}:\n";
    echo "    Likes: {$sampleEpisode->likes}\n";
    echo "    Duration: " . ($sampleEpisode->duration ? $sampleEpisode->duration . ' min' : 'N/A') . "\n";
}
