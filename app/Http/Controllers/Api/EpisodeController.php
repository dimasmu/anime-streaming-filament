<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Anime;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EpisodeController extends Controller
{
    /**
     * Display latest episodes.
     *
     * @return JsonResponse
     */
    public function latest(): JsonResponse
    {
        $episodes = Episode::with(['anime'])
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $episodes,
        ]);
    }

    /**
     * Display the specified episode.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $episode = Episode::with(['anime.genres', 'anime.studio', 'anime.categories'])
            ->where('id', $id)
            ->where('is_published', true)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $episode,
        ]);
    }

    /**
     * Increment episode view count.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function incrementView(int $id): JsonResponse
    {
        $episode = Episode::findOrFail($id);
        $episode->increment('views');

        return response()->json([
            'success' => true,
            'message' => 'View count incremented',
            'data' => [
                'views' => $episode->fresh()->views,
            ],
        ]);
    }

    /**
     * Toggle episode like.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function toggleLike(Request $request, int $id): JsonResponse
    {
        $episode = Episode::findOrFail($id);

        // In a real application, you would track user likes in a separate table
        // For now, we'll just increment/decrement the likes count
        $action = $request->input('action', 'increment');

        if ($action === 'increment') {
            $episode->increment('likes');
        } elseif ($action === 'decrement') {
            $episode->decrement('likes');
        }

        return response()->json([
            'success' => true,
            'message' => 'Like toggled',
            'data' => [
                'likes' => $episode->fresh()->likes,
            ],
        ]);
    }

    /**
     * Get episode by anime slug and episode number.
     *
     * @param string $animeSlug
     * @param int $episodeNumber
     * @return JsonResponse
     */
    public function getByAnime(string $animeSlug, int $episodeNumber): JsonResponse
    {
        $anime = Anime::where('slug', $animeSlug)
            ->where('is_published', true)
            ->firstOrFail();

        $episode = $anime->episodes()
            ->where('episode_number', $episodeNumber)
            ->where('is_published', true)
            ->firstOrFail();

        // Increment view count
        $episode->increment('views');

        // Load relationships
        $episode->load(['anime.genres', 'anime.studio', 'anime.categories']);

        return response()->json([
            'success' => true,
            'data' => $episode,
        ]);
    }
}
