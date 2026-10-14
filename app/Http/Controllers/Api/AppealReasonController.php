<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppealReason;
use Illuminate\Http\JsonResponse;

class AppealReasonController extends Controller
{
    /**
     * Liste les motifs actuellement disponibles pour introduire un recours.
     */
    public function index(): JsonResponse
    {
        $reasons = AppealReason::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'requires_attachment',
                'requires_description',
            ]);

        return response()->json([
            'success' => true,
            'data' => $reasons,
        ]);
    }
}
