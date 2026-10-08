<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tutorial;
use Illuminate\Http\JsonResponse;

class TutorialController extends Controller
{
    public function index(): JsonResponse
    {
        $tutorials = Tutorial::query()
            ->orderByRaw('youtube_playlist_id IS NULL')
            ->orderBy('youtube_playlist_id')
            ->orderBy('position')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tutorials,
        ]);
    }

    public function show(Tutorial $tutorial): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $tutorial,
        ]);
    }
}
