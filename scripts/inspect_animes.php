<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = App\Models\Anime::query()
    ->select(['id', 'title', 'mal_id', 'mal_url', 'episodes_count'])
    ->orderBy('id')
    ->get();

foreach ($rows as $row) {
    echo sprintf(
        "#%d | %s | mal_id=%s | episodes_count=%s\n",
        $row->id,
        $row->title,
        $row->mal_id ?? 'null',
        $row->episodes_count ?? 'null',
    );
}
