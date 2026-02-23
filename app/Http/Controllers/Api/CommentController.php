<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**
     * Display a listing of comments.
     */
    public function index(Request $request)
    {
        $request->validate([
            'anime_id' => 'nullable|exists:animes,id',
            'episode_id' => 'nullable|exists:episodes,id',
            'without_spoilers' => 'nullable|boolean',
        ]);

        $query = Comment::visible()->with(['user' => function ($query) {
            $query->select('id', 'name');
        }]);

        if ($request->has('anime_id')) {
            $query->forAnime($request->anime_id);
        }

        if ($request->has('episode_id')) {
            $query->forEpisode($request->episode_id);
        }

        if ($request->boolean('without_spoilers')) {
            $query->withoutSpoilers();
        }

        $comments = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    /**
     * Store a newly created comment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'anime_id' => 'required_without:episode_id|exists:animes,id',
            'episode_id' => 'required_without:anime_id|exists:episodes,id',
            'content' => 'required|string|max:5000',
            'is_spoiler' => 'nullable|boolean',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'anime_id' => $request->anime_id,
            'episode_id' => $request->episode_id,
            'content' => $request->content,
            'is_spoiler' => $request->boolean('is_spoiler', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment created successfully',
            'data' => $comment->load(['user:id,name', 'anime:id,title', 'episode:id,title']),
        ], 201);
    }

    /**
     * Display the specified comment.
     */
    public function show($id)
    {
        $comment = Comment::visible()->with(['user', 'anime', 'episode'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $comment,
        ]);
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'content' => 'required|string|max:5000',
            'is_spoiler' => 'nullable|boolean',
        ]);

        $comment->update([
            'content' => $request->content,
            'is_spoiler' => $request->boolean('is_spoiler', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data' => $comment->load(['user:id,name', 'anime:id,title', 'episode:id,title']),
        ]);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy($id)
    {
        $comment = Comment::where('user_id', Auth::id())->findOrFail($id);
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Get user's own comments.
     */
    public function myComments()
    {
        $comments = Comment::where('user_id', Auth::id())
            ->with(['anime:id,title', 'episode:id,title'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }
}
