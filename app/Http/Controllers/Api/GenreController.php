<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Anime;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    /**
     * Display a listing of genres.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $genres = Genre::withCount('animes')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $genres,
        ]);
    }

    /**
     * Display the specified genre.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        $genre = Genre::withCount('animes')
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $genre,
        ]);
    }

    /**
     * Display animes for the specified genre.
     *
     * @param string $slug
     * @param Request $request
     * @return JsonResponse
     */
    public function animes(string $slug, Request $request): JsonResponse
    {
        $genre = Genre::where('slug', $slug)->firstOrFail();

        $query = $genre->animes()
            ->with(['genres', 'studio'])
            ->where('is_published', true)
            ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
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
            'data' => [
                'genre' => $genre,
                'animes' => $animes->items(),
                'pagination' => [
                    'current_page' => $animes->currentPage(),
                    'last_page' => $animes->lastPage(),
                    'per_page' => $animes->perPage(),
                    'total' => $animes->total(),
                    'from' => $animes->firstItem(),
                    'to' => $animes->lastItem(),
                ],
            ],
        ]);
    }
}
