<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use Illuminate\Http\JsonResponse;

class EpisodeController extends Controller
{
    public function show(Episode $episode): JsonResponse
    {
        $episode->load(['anime', 'videoUploadType']);

        // Increment view count
        $episode->increment('views');

        return response()->json($episode);
    }
}
