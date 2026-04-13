<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        \App\Console\Commands\SyncAnimeMalMetadata::class,
        \App\Console\Commands\ImportPopularAnimeFromMal::class,
        \App\Console\Commands\SeedPlaceholderEpisodes::class,
        \App\Console\Commands\PublishAnime::class,
        \App\Console\Commands\PublishEpisodes::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
