<?php

use App\Http\Controllers\Api\AnimeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\Api\WatchHistoryController;
use Illuminate\Support\Facades\Route;

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

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// Public anime endpoints
Route::prefix('animes')->group(function () {
    Route::get('/', [AnimeController::class, 'index']);
    Route::get('/slug/{slug}', [AnimeController::class, 'getBySlug']);
    Route::get('/{id}', [AnimeController::class, 'show']);
});

// Public episode endpoints
Route::prefix('episodes')->group(function () {
    Route::get('/{id}', [EpisodeController::class, 'show']);
});

// Protected watch history endpoints
Route::middleware('auth:sanctum')->prefix('watch')->group(function () {
    Route::post('/progress', [WatchHistoryController::class, 'updateProgress']);
    Route::get('/history', [WatchHistoryController::class, 'history']);
    Route::get('/continue-watching', [WatchHistoryController::class, 'continueWatching']);
    Route::post('/mark-complete', [WatchHistoryController::class, 'markComplete']);
});

// Protected favorites endpoints
Route::middleware('auth:sanctum')->prefix('favorites')->group(function () {
    Route::get('/', [AnimeController::class, 'favorites']);
    Route::post('/{anime}', [AnimeController::class, 'toggleFavorite']);
});

// Public comment endpoints
Route::prefix('comments')->group(function () {
    Route::get('/', [CommentController::class, 'index']);
    Route::get('/{id}', [CommentController::class, 'show']);
});

// Protected comment endpoints (require authentication)
Route::middleware('auth:sanctum')->prefix('comments')->group(function () {
    Route::post('/', [CommentController::class, 'store']);
    Route::put('/{id}', [CommentController::class, 'update']);
    Route::delete('/{id}', [CommentController::class, 'destroy']);
    Route::get('/my-comments', [CommentController::class, 'myComments']);
});

// Protected bookmark endpoints (require authentication)
Route::middleware('auth:sanctum')->prefix('bookmarks')->group(function () {
    Route::get('/', [BookmarkController::class, 'index']);
    Route::post('/', [BookmarkController::class, 'store']);
    Route::get('/check', [BookmarkController::class, 'check']);
    Route::get('/{id}', [BookmarkController::class, 'show']);
    Route::put('/{id}', [BookmarkController::class, 'update']);
    Route::delete('/{id}', [BookmarkController::class, 'destroy']);
});
