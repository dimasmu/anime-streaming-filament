<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of user's bookmarks.
     */
    public function index(Request $request)
    {
        $request->validate([
            'anime_id' => 'nullable|exists:animes,id',
            'episode_id' => 'nullable|exists:episodes,id',
        ]);

        $query = Bookmark::forUser(Auth::id())
            ->with(['anime:id,title,poster_image', 'episode:id,title,episode_number']);

        if ($request->has('anime_id')) {
            $query->forAnime($request->anime_id);
        }

        if ($request->has('episode_id')) {
            $query->forEpisode($request->episode_id);
        }

        $bookmarks = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bookmarks,
        ]);
    }

    /**
     * Store a newly created bookmark.
     */
    public function store(Request $request)
    {
        $request->validate([
            'anime_id' => 'required_without:episode_id|exists:animes,id',
            'episode_id' => 'required_without:anime_id|exists:episodes,id',
            'notes' => 'nullable|string|max:1000',
            'timestamp' => 'nullable|integer|min:0',
        ]);

        $bookmark = Bookmark::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'anime_id' => $request->anime_id,
                'episode_id' => $request->episode_id,
            ],
            [
                'notes' => $request->notes,
                'timestamp' => $request->timestamp,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Bookmark saved successfully',
            'data' => $bookmark->load(['anime:id,title', 'episode:id,title,episode_number']),
        ], 201);
    }

    /**
     * Display the specified bookmark.
     */
    public function show($id)
    {
        $bookmark = Bookmark::forUser(Auth::id())
            ->with(['anime', 'episode'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $bookmark,
        ]);
    }

    /**
     * Update the specified bookmark.
     */
    public function update(Request $request, $id)
    {
        $bookmark = Bookmark::forUser(Auth::id())->findOrFail($id);

        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'timestamp' => 'nullable|integer|min:0',
        ]);

        $bookmark->update($request->only(['notes', 'timestamp']));

        return response()->json([
            'success' => true,
            'message' => 'Bookmark updated successfully',
            'data' => $bookmark->load(['anime:id,title', 'episode:id,title']),
        ]);
    }

    /**
     * Remove the specified bookmark.
     */
    public function destroy($id)
    {
        $bookmark = Bookmark::forUser(Auth::id())->findOrFail($id);
        $bookmark->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bookmark deleted successfully',
        ]);
    }

    /**
     * Check if user has bookmarked specific content.
     */
    public function check(Request $request)
    {
        $request->validate([
            'anime_id' => 'nullable|exists:animes,id',
            'episode_id' => 'nullable|exists:episodes,id',
        ]);

        if (! $request->anime_id && ! $request->episode_id) {
            return response()->json([
                'success' => false,
                'message' => 'Either anime_id or episode_id is required',
            ], 422);
        }

        $bookmark = Bookmark::forUser(Auth::id())
            ->where('anime_id', $request->anime_id)
            ->where('episode_id', $request->episode_id)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'is_bookmarked' => $bookmark !== null,
                'bookmark' => $bookmark,
            ],
        ]);
    }
}
