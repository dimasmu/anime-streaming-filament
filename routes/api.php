<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnimeController;
use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\StudioController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API endpoints
Route::prefix('v1')->group(function () {

    // Anime endpoints
    Route::prefix('animes')->group(function () {
        Route::get('/', [AnimeController::class, 'index']);
        Route::get('/featured', [AnimeController::class, 'featured']);
        Route::get('/latest', [AnimeController::class, 'latest']);
        Route::get('/trending', [AnimeController::class, 'trending']);
        Route::get('/top/today', [AnimeController::class, 'topToday']);
        Route::get('/top/week', [AnimeController::class, 'topWeek']);
        Route::get('/top/month', [AnimeController::class, 'topMonth']);
        Route::get('/episode/{slug}', [AnimeController::class, 'getEpisodesBySlug']);
        Route::get('/{slug}', [AnimeController::class, 'show']);
        Route::get('/{slug}/episodes', [AnimeController::class, 'episodes']);
    });

    // Episode endpoints
    Route::prefix('episodes')->group(function () {
        Route::get('/latest', [EpisodeController::class, 'latest']);
        Route::get('/{id}', [EpisodeController::class, 'show']);
        Route::post('/{id}/view', [EpisodeController::class, 'incrementView']);
        Route::post('/{id}/like', [EpisodeController::class, 'toggleLike']);
    });

    // Watch endpoint (get episode by anime slug and episode number)
    Route::get('/watch/{animeSlug}/{episodeNumber}', [EpisodeController::class, 'getByAnime']);

    // Genre endpoints
    Route::prefix('genres')->group(function () {
        Route::get('/', [GenreController::class, 'index']);
        Route::get('/{slug}', [GenreController::class, 'show']);
        Route::get('/{slug}/animes', [GenreController::class, 'animes']);
    });

    // Studio endpoints
    Route::prefix('studios')->group(function () {
        Route::get('/', [StudioController::class, 'index']);
        Route::get('/{slug}', [StudioController::class, 'show']);
        Route::get('/{slug}/animes', [StudioController::class, 'animes']);
    });

    // Category endpoints
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{slug}', [CategoryController::class, 'show']);
        Route::get('/{slug}/animes', [CategoryController::class, 'animes']);
    });

    // Search endpoint
    Route::get('/search', [AnimeController::class, 'search']);

    // Comment endpoints
    Route::prefix('comments')->group(function () {
        // Get comments (public)
        Route::get('/get/anime/{slug}', [CommentController::class, 'getAnimeComments']);
        Route::get('/get/episode/{animeSlug}/{episodeNumber}', [CommentController::class, 'getEpisodeComments']);

        // Save/manage comments (require authentication)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/save', [CommentController::class, 'store']);
            Route::put('/save/{id}', [CommentController::class, 'update']);
            Route::delete('/save/{id}', [CommentController::class, 'destroy']);
            Route::post('/save/{id}/like', [CommentController::class, 'toggleLike']);
        });
    });
});
