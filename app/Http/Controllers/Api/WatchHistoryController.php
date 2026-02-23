<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\WatchHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchHistoryController extends Controller
{
    public function updateProgress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'episode_id' => 'required|exists:episodes,id',
            'current_position' => 'required|integer|min:0',
            'total_duration' => 'nullable|integer',
        ]);

        $user = auth()->user();

        $episode = Episode::findOrFail($validated['episode_id']);

        $history = WatchHistory::updateOrCreate(
            [
                'user_id' => $user->id,
                'episode_id' => $episode->id,
            ],
            [
                'anime_id' => $episode->anime_id,
                'current_position' => $validated['current_position'],
                'total_duration' => $validated['total_duration'] ?? $episode->duration * 60, // Convert minutes to seconds
                'last_watched_at' => now(),
            ]
        );

        // Calculate if episode is completed (watched 90% or more)
        if ($history->total_duration > 0) {
            $percentWatched = ($history->current_position / $history->total_duration) * 100;
            if ($percentWatched >= 90 && ! $history->is_completed) {
                $history->update([
                    'is_completed' => true,
                    'completed_at' => now(),
                ]);
            }
        }

        return response()->json($history);
    }

    public function history(): JsonResponse
    {
        $user = auth()->user();

        $history = $user->watchHistory()
            ->with(['anime', 'episode'])
            ->orderBy('last_watched_at', 'desc')
            ->paginate(50);

        return response()->json($history);
    }

    public function continueWatching(): JsonResponse
    {
        $user = auth()->user();

        $continueWatching = $user->watchHistory()
            ->with(['anime', 'episode'])
            ->inProgress()
            ->orderBy('last_watched_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($continueWatching);
    }

    public function markComplete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'episode_id' => 'required|exists:episodes,id',
        ]);

        $user = auth()->user();

        $history = WatchHistory::where('user_id', $user->id)
            ->where('episode_id', $validated['episode_id'])
            ->first();

        if ($history) {
            $history->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
