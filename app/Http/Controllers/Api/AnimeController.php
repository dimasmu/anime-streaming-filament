<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Anime::query()
            ->with(['genres', 'categories', 'studio'])
            ->withCount('episodes')
            ->published();

        // Filter by adult content
        $includeAdult = $request->boolean('include_adult', false);
        $query->adult($includeAdult);

        // Filter by genre
        if ($request->has('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('slug', $request->genre);
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by quality
        if ($request->has('quality')) {
            $query->byQuality($request->quality);
        }

        // Filter by sub/dub availability
        if ($request->has('tab')) {
            if ($request->tab === 'sub') {
                $query->hasSub();
            } elseif ($request->tab === 'dub') {
                $query->hasDub();
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('japanese_title', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $animes = $query->paginate($perPage);

        return response()->json([
            'data' => $animes->items(),
            'meta' => [
                'current_page' => $animes->currentPage(),
                'per_page' => $animes->perPage(),
                'total' => $animes->total(),
                'last_page' => $animes->lastPage(),
            ],
        ]);
    }

    public function show(Anime $anime): JsonResponse
    {
        $anime->load(['genres', 'categories', 'studio', 'episodes' => function ($query) {
            $query->published()->orderBy('episode_number');
        }]);

        return response()->json($anime);
    }

    public function getBySlug(string $slug): JsonResponse
    {
        $anime = Anime::where('slug', $slug)
            ->with(['genres', 'categories', 'studio', 'episodes' => function ($query) {
                $query->published()->orderBy('episode_number');
            }])
            ->firstOrFail();

        return response()->json($anime);
    }

    public function favorites(Request $request): JsonResponse
    {
        $user = $request->user();

        $favorites = $user->favorites()
            ->with(['genres', 'categories', 'studio'])
            ->withCount('episodes')
            ->published()
            ->paginate(20);

        return response()->json([
            'data' => $favorites->items(),
            'meta' => [
                'current_page' => $favorites->currentPage(),
                'per_page' => $favorites->perPage(),
                'total' => $favorites->total(),
                'last_page' => $favorites->lastPage(),
            ],
        ]);
    }

    public function toggleFavorite(Request $request, Anime $anime): JsonResponse
    {
        $user = $request->user();

        $isFavorited = $user->favorites()->where('anime_id', $anime->id)->exists();

        if ($isFavorited) {
            $user->favorites()->detach($anime->id);

            return response()->json([
                'success' => true,
                'favorited' => false,
            ]);
        } else {
            $user->favorites()->attach($anime->id);

            return response()->json([
                'success' => true,
                'favorited' => true,
            ]);
        }
    }
}
