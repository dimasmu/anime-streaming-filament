<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnimeController extends Controller
{
    /**
     * Display a listing of animes.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Anime::with(['genres', 'studio', 'categories'])
            ->where('is_published', true)
            ->orderBy('created_at', 'desc');

        // Filter by genre
        if ($request->has('genre')) {
            $genreSlug = $request->query('genre');
            $query->whereHas('genres', function ($q) use ($genreSlug) {
                $q->where('slug', $genreSlug);
            });
        }

        // Filter by studio
        if ($request->has('studio')) {
            $studioSlug = $request->query('studio');
            $query->whereHas('studio', function ($q) use ($studioSlug) {
                $q->where('slug', $studioSlug);
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $categorySlug = $request->query('category');
            $query->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filter by year
        if ($request->has('year')) {
            $query->whereYear('release_date', $request->query('year'));
        }

        // Search
        if ($request->has('search')) {
            $searchTerm = $request->query('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Sort
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');

        if ($sortBy === 'rating') {
            $query->orderBy('rating', $sortOrder);
        } elseif ($sortBy === 'title') {
            $query->orderBy('title', $sortOrder);
        } elseif ($sortBy === 'release_date') {
            $query->orderBy('release_date', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginate
        $perPage = $request->query('per_page', 12);
        $animes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $animes->items(),
            'pagination' => [
                'current_page' => $animes->currentPage(),
                'last_page' => $animes->lastPage(),
                'per_page' => $animes->perPage(),
                'total' => $animes->total(),
                'from' => $animes->firstItem(),
                'to' => $animes->lastItem(),
            ],
        ]);
    }

    /**
     * Display featured animes.
     *
     * @return JsonResponse
     */
    public function featured(): JsonResponse
    {
        $animes = Anime::with(['genres', 'studio'])
            ->where('is_published', true)
            ->where('is_featured', true)
            ->orderBy('rating', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $animes,
        ]);
    }

    /**
     * Display latest animes.
     *
     * @return JsonResponse
     */
    public function latest(): JsonResponse
    {
        $animes = Anime::with(['genres', 'studio'])
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $animes,
        ]);
    }

    /**
     * Display trending animes.
     *
     * @return JsonResponse
     */
    public function trending(): JsonResponse
    {
        $animes = Anime::with(['genres', 'studio', 'episodes'])
            ->where('is_published', true)
            ->withCount('episodes')
            ->orderBy('rating', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $animes,
        ]);
    }

    /**
     * Display the specified anime.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        $anime = Anime::with(['genres', 'studio', 'categories', 'episodes' => function ($query) {
            $query->where('is_published', true)
                  ->orderBy('episode_number', 'asc');
        }])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Increment views count for the anime
        $anime->increment('views');

        return response()->json([
            'success' => true,
            'data' => $anime,
        ]);
    }

    /**
     * Display episodes for the specified anime.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function episodes(string $slug): JsonResponse
    {
        $anime = Anime::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $episodes = $anime->episodes()
            ->where('is_published', true)
            ->orderBy('episode_number', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'anime' => $anime,
                'episodes' => $episodes,
            ],
        ]);
    }

    /**
     * Get episodes with full anime details by slug.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function getEpisodesBySlug(string $slug): JsonResponse
    {
        $anime = Anime::with(['genres', 'studio', 'categories'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $episodes = $anime->episodes()
            ->where('is_published', true)
            ->orderBy('episode_number', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'anime' => [
                    'id' => $anime->id,
                    'title' => $anime->title,
                    'slug' => $anime->slug,
                    'season_number' => $anime->season_number,
                    'seasons_total' => $anime->seasons_total,
                    'description' => $anime->description,
                    'synopsis' => $anime->synopsis,
                    'poster_image' => $anime->poster_image,
                    'poster_image_url' => $anime->poster_image_url,
                    'cover_image' => $anime->cover_image,
                    'cover_image_url' => $anime->cover_image_url,
                    'trailer_url' => $anime->trailer_url,
                    'type' => $anime->type,
                    'status' => $anime->status,
                    'episodes_count' => $anime->episodes_count,
                    'duration' => $anime->duration,
                    'release_date' => $anime->release_date,
                    'rating' => $anime->rating,
                    'views' => $anime->views,
                    'source' => $anime->source,
                    'is_featured' => $anime->is_featured,
                    'genres' => $anime->genres,
                    'studio' => $anime->studio,
                    'categories' => $anime->categories,
                ],
                'episodes' => $episodes->map(function ($episode, $index) use ($episodes, $anime) {
                    $data = [
                        'id' => $episode->id,
                        'episode_number' => $episode->episode_number,
                    ];

                    // Add previous episode URL if exists
                    if ($index > 0) {
                        $prevEpisode = $episodes[$index - 1];
                        $data['previous_episode_url'] = url("/api/v1/watch/{$anime->slug}/{$prevEpisode->episode_number}");
                    }

                    // Add next episode URL if exists
                    if ($index < $episodes->count() - 1) {
                        $nextEpisode = $episodes[$index + 1];
                        $data['next_episode_url'] = url("/api/v1/watch/{$anime->slug}/{$nextEpisode->episode_number}");
                    }

                    return $data;
                }),
                'total_episodes' => $episodes->count(),
            ],
        ]);
    }

    /**
     * Search animes.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $searchTerm = $request->query('q');

        if (empty($searchTerm)) {
            return response()->json([
                'success' => false,
                'message' => 'Search term is required',
            ], 400);
        }

        $animes = Anime::with(['genres', 'studio'])
            ->where('is_published', true)
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', '%' . $searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $searchTerm . '%')
                      ->orWhere('synopsis', 'like', '%' . $searchTerm . '%');
            })
            ->orderBy('rating', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $animes,
        ]);
    }

    /**
     * Get top 10 anime for today (most views in last 24 hours).
     *
     * @return JsonResponse
     */
    public function topToday(): JsonResponse
    {
        $animes = Anime::with(['genres', 'studio'])
            ->where('is_published', true)
            ->where('updated_at', '>=', now()->subDay())
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $animes,
        ]);
    }

    /**
     * Get top 10 anime for this week (most views in last 7 days).
     *
     * @return JsonResponse
     */
    public function topWeek(): JsonResponse
    {
        $animes = Anime::with(['genres', 'studio'])
            ->where('is_published', true)
            ->where('updated_at', '>=', now()->subWeek())
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $animes,
        ]);
    }

    /**
     * Get top 10 anime for this month (most views in last 30 days).
     *
     * @return JsonResponse
     */
    public function topMonth(): JsonResponse
    {
        $animes = Anime::with(['genres', 'studio'])
            ->where('is_published', true)
            ->where('updated_at', '>=', now()->subMonth())
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $animes,
        ]);
    }
}
