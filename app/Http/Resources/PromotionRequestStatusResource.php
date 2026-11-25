<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionRequestStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->resource;
        $promotionRequest = $status['request'];

        return [
            'academic_year' => $status['academic_year']?->only(['id', 'name']),
            'can_submit' => $status['can_submit'],
            'message' => $status['message'],
            'request' => $promotionRequest ? [
                'id' => $promotionRequest->id,
                'status' => $promotionRequest->status,
                'created_at' => $promotionRequest->created_at?->toIso8601String(),
                'admin_comment' => $promotionRequest->admin_comment,
            ] : null,
        ];
    }
}
