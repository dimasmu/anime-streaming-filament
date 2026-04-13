<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Anime;
use Illuminate\Support\Facades\DB;

echo "Randomizing anime view counts...\n";
echo str_repeat('-', 50) . "\n";

// Get all anime
$animes = Anime::all();
$total = $animes->count();

echo "Found {$total} anime to update.\n\n";

$updated = 0;

// Use raw SQL for better performance
DB::beginTransaction();

try {
    foreach ($animes as $anime) {
        // Generate random views based on status and popularity
        // Featured anime get more views
        if ($anime->is_featured) {
            $minViews = 50000;
            $maxViews = 500000;
        } else {
            $minViews = 1000;
            $maxViews = 100000;
        }

        $randomViews = rand($minViews, $maxViews);

        DB::table('animes')->where('id', $anime->id)->update(['views' => $randomViews]);
        $updated++;

        if ($updated % 100 == 0) {
            echo "Updated {$updated}/{$total}...\n";
        }
    }

    DB::commit();
    echo "Committed transaction.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Successfully randomized views for {$updated} anime!\n";

// Show some statistics
$stats = Anime::selectRaw('
    MIN(views) as min_views,
    MAX(views) as max_views,
    AVG(views) as avg_views
')->first();

echo "\nStatistics:\n";
echo "  Min views: " . number_format($stats->min_views) . "\n";
echo "  Max views: " . number_format($stats->max_views) . "\n";
echo "  Avg views: " . number_format($stats->avg_views) . "\n";

// Show top 5 most viewed
echo "\nTop 5 Most Viewed:\n";
$top5 = Anime::orderBy('views', 'desc')->limit(5)->get();
foreach ($top5 as $anime) {
    echo "  - {$anime->title}: " . number_format($anime->views) . " views\n";
}
