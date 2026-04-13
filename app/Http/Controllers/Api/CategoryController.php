<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = Category::withCount('animes')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Display the specified category.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        $category = Category::withCount('animes')
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Display animes for the specified category.
     *
     * @param string $slug
     * @param Request $request
     * @return JsonResponse
     */
    public function animes(string $slug, Request $request): JsonResponse
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $query = $category->animes()
            ->with(['genres', 'studio', 'categories'])
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
                'category' => $category,
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
