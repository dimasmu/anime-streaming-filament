<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Anime;
use App\Models\Episode;
use Illuminate\Support\Facades\DB;

echo "Populating anime and episode data...\n";
echo str_repeat('-', 50) . "\n";

DB::beginTransaction();

try {
    echo "Step 1: Updating anime data...\n";

    // Get all anime with episode counts
    $animes = Anime::withCount('episodes')->get();
    $totalAnimes = $animes->count();
    $updatedAnimes = 0;

    foreach ($animes as $anime) {
        $actualEpisodeCount = $anime->episodes_count;

        $updates = [];

        // Set correct episodes_count
        $updates['episodes_count'] = $actualEpisodeCount;

        // Set status based on episode count
        if ($actualEpisodeCount >= 12) {
            $updates['status'] = rand(1, 100) <= 70 ? 'completed' : 'ongoing';
        } elseif ($actualEpisodeCount > 0) {
            $updates['status'] = rand(1, 100) <= 50 ? 'ongoing' : 'upcoming';
        } else {
            $updates['status'] = 'upcoming';
        }

        // Add rating (6.0 - 9.5)
        if (!$anime->rating) {
            $updates['rating'] = rand(60, 95) / 10;
        }

        // Add duration
        if (!$anime->duration) {
            $updates['duration'] = $anime->type === 'movie' ? rand(90, 150) : rand(20, 28);
        }

        // Add release date
        if (!$anime->release_date) {
            $year = rand(2015, 2026);
            $month = rand(1, 12);
            $day = rand(1, 28);
            $updates['release_date'] = sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        // Add source
        if (!$anime->source) {
            $sources = ['manga', 'light novel', 'original', 'visual novel', 'game', 'web manga'];
            $updates['source'] = $sources[array_rand($sources)];
        }

        DB::table('animes')->where('id', $anime->id)->update($updates);
        $updatedAnimes++;

        if ($updatedAnimes % 100 == 0) {
            echo "Updated {$updatedAnimes}/{$totalAnimes} anime...\n";
        }
    }

    echo "\nStep 2: Updating episode likes and durations (bulk)...\n";

    // Update all episodes with random likes (0-5000)
    $episodes = Episode::all();
    $totalEpisodes = $episodes->count();
    $updatedEpisodes = 0;
    $batchSize = 500;

    foreach ($episodes->chunk($batchSize) as $chunk) {
        foreach ($chunk as $episode) {
            DB::table('episodes')
                ->where('id', $episode->id)
                ->update([
                    'likes' => rand(0, 5000),
                    'duration' => $episode->duration ?: rand(20, 28)
                ]);
            $updatedEpisodes++;
        }

        echo "Updated {$updatedEpisodes}/{$totalEpisodes} episodes...\n";
    }

    DB::commit();

    echo "\n✅ Successfully updated data!\n";
    echo "  Animes updated: {$updatedAnimes}\n";
    echo "  Episodes updated: {$updatedEpisodes}\n";

    // Show statistics
    echo "\nAnime Statistics:\n";
    $statusCounts = Anime::selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->get();

    foreach ($statusCounts as $stat) {
        echo "  {$stat->status}: {$stat->count}\n";
    }

    $avgRating = Anime::avg('rating');
    echo "\nAverage Rating: " . number_format($avgRating, 2) . "\n";

    // Show episode likes statistics
    $totalLikes = Episode::sum('likes');
    $avgLikes = Episode::avg('likes');
    echo "\nEpisode Likes:\n";
    echo "  Total: " . number_format($totalLikes) . "\n";
    echo "  Average: " . number_format($avgLikes, 0) . "\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
