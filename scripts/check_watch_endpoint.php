<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Anime;
use App\Models\Episode;

$slug = 'b-daman-crossfire-6bf9ee';

echo "Checking anime with slug: $slug\n";
echo str_repeat('-', 50) . "\n";

// Check if anime exists
$anime = Anime::where('slug', $slug)->first();

if (!$anime) {
    echo "❌ Anime not found with slug: $slug\n\n";

    // Try to find similar slugs
    echo "Looking for B-Daman CrossFire...\n";
    $similar = Anime::where('title', 'LIKE', '%B-Daman%')->get();

    if ($similar->isEmpty()) {
        echo "No B-Daman anime found in database.\n";
    } else {
        echo "\nFound " . $similar->count() . " similar anime:\n";
        foreach ($similar as $a) {
            echo "  - Title: {$a->title}\n";
            echo "    Slug: {$a->slug}\n";
            echo "    Published: " . ($a->is_published ? 'Yes' : 'No') . "\n";
            echo "    Episodes: " . $a->episodes()->count() . "\n";
            echo "\n";
        }
    }
} else {
    echo "✅ Anime found!\n";
    echo "  Title: {$anime->title}\n";
    echo "  Slug: {$anime->slug}\n";
    echo "  Published: " . ($anime->is_published ? 'Yes' : 'No') . "\n";
    echo "  Episodes: " . $anime->episodes()->count() . "\n\n";

    // Check episode 1
    $episode = $anime->episodes()->where('episode_number', 1)->first();

    if (!$episode) {
        echo "❌ Episode 1 not found\n";
    } else {
        echo "✅ Episode 1 found!\n";
        echo "  Title: {$episode->title}\n";
        echo "  Published: " . ($episode->is_published ? 'Yes' : 'No') . "\n";
        echo "  Duration: {$episode->duration}\n";
    }
}
