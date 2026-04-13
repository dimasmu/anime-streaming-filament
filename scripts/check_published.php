<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$published = App\Models\Anime::where('is_published', true)->count();
$unpublished = App\Models\Anime::where('is_published', false)->count();
$total = App\Models\Anime::count();

echo "═══════════════════════════════════════════\n";
echo "Anime Publication Status\n";
echo "═══════════════════════════════════════════\n";
echo "Published:   {$published}\n";
echo "Unpublished: {$unpublished}\n";
echo "Total:       {$total}\n";
echo "═══════════════════════════════════════════\n";

$sample = App\Models\Anime::where('is_published', true)
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get(['id', 'title', 'is_published']);

echo "\nSample published anime:\n";
foreach ($sample as $anime) {
    echo "  #{$anime->id} - {$anime->title}\n";
}
