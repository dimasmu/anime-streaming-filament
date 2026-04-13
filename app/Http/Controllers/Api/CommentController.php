<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Anime;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Get comments for an anime
     */
    public function getAnimeComments(string $slug): JsonResponse
    {
        $anime = Anime::where('slug', $slug)->firstOrFail();

        $comments = Comment::where('anime_id', $anime->id)
            ->whereNull('episode_id')
            ->whereNull('parent_id')
            ->with(['replies', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $comments->items(),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ],
        ]);
    }

    /**
     * Get comments for an episode
     */
    public function getEpisodeComments(string $animeSlug, int $episodeNumber): JsonResponse
    {
        $anime = Anime::where('slug', $animeSlug)->firstOrFail();
        $episode = Episode::where('anime_id', $anime->id)
            ->where('episode_number', $episodeNumber)
            ->firstOrFail();

        $comments = Comment::where('episode_id', $episode->id)
            ->whereNull('parent_id')
            ->with(['replies', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $comments->items(),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ],
        ]);
    }

    /**
     * Store a new comment
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'anime_id' => 'nullable|exists:animes,id',
            'episode_id' => 'nullable|exists:episodes,id',
            'parent_id' => 'nullable|exists:comments,id',
            'comment' => 'required|string|min:1|max:1000',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'anime_id' => $validated['anime_id'] ?? null,
            'episode_id' => $validated['episode_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'comment' => $validated['comment'],
        ]);

        $comment->load(['user', 'replies']);

        return response()->json([
            'success' => true,
            'message' => 'Comment posted successfully',
            'data' => $comment,
        ], 201);
    }

    /**
     * Update a comment
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'comment' => 'required|string|min:1|max:1000',
        ]);

        $comment->update(['comment' => $validated['comment']]);
        $comment->load(['user', 'replies']);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data' => $comment,
        ]);
    }

    /**
     * Delete a comment
     */
    public function destroy(int $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Toggle like on a comment
     */
    public function toggleLike(int $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);
        $user = Auth::user();

        $isLiked = $comment->likedBy()->where('user_id', $user->id)->exists();

        if ($isLiked) {
            $comment->likedBy()->detach($user->id);
            $comment->decrement('likes_count');
            $message = 'Comment unliked';
            $liked = false;
        } else {
            $comment->likedBy()->attach($user->id);
            $comment->increment('likes_count');
            $message = 'Comment liked';
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'liked' => $liked,
                'likes_count' => $comment->fresh()->likes_count,
            ],
        ]);
    }
}

