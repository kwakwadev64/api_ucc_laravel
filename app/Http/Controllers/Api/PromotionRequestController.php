<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromotionRequestStatusResource;
use App\Models\AcademicYear;
use App\Models\PromotionRequest;
use App\Services\PromotionRequestService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PromotionRequestController extends Controller
{
    public function __construct(private readonly PromotionRequestService $promotionRequests) {}

    public function current(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new PromotionRequestStatusResource(
                $this->promotionRequests->currentStatusFor($request->user())
            ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $availability = $this->promotionRequests->currentStatusFor($user);
        $academicYear = $availability['academic_year'];

        // Une décision définitive ne réautorise pas une demande la même année.
        if ($availability['request']) {
            return $this->conflictResponse($availability['request'], $academicYear);
        }

        if (! $availability['can_submit']) {
            return response()->json([
                'success' => false,
                'message' => $availability['message'],
            ], 422);
        }

        $validated = $request->validate([
            'target_promotion_id' => 'required|exists:promotions,id',
            'proof_file' => 'required|image|max:4096',
        ], [
            'target_promotion_id.required' => 'Veuillez sélectionner la promotion souhaitée.',
            'target_promotion_id.exists' => 'La promotion sélectionnée est invalide.',
            'proof_file.required' => 'Veuillez joindre votre justificatif.',
            'proof_file.image' => 'Le justificatif doit être une image.',
            'proof_file.max' => 'Le justificatif ne doit pas dépasser 4 Mo.',
        ]);

        if ((int) $validated['target_promotion_id'] === (int) $user->promotion_id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà inscrit dans cette promotion.',
            ], 422);
        }

        $path = $request->file('proof_file')->store('proofs', 'public');

        try {
            $promotionRequest = DB::transaction(function () use ($user, $academicYear, $validated, $path) {
                return PromotionRequest::create([
                    'user_id' => $user->id,
                    'academic_year_id' => $academicYear->id,
                    'current_promotion_id' => $user->promotion_id,
                    'target_promotion_id' => $validated['target_promotion_id'],
                    'proof_file' => $path,
                    'status' => 'pending',
                ]);
            });
        } catch (QueryException $exception) {
            Storage::disk('public')->delete($path);

            // La contrainte unique couvre aussi deux envois simultanés.
            $existingRequest = $this->promotionRequests->findForYear($user, $academicYear);
            if ($existingRequest) {
                return $this->conflictResponse($existingRequest, $academicYear);
            }

            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => 'Votre demande de passage de promotion a été envoyée avec succès.',
            'data' => $promotionRequest->load(['academicYear', 'currentPromotion', 'targetPromotion']),
        ], 201);
    }

    private function conflictResponse(PromotionRequest $request, AcademicYear $academicYear): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->promotionRequests->alreadySubmittedMessage($request),
            'status' => $request->status,
            'academic_year' => $academicYear->name,
        ], 409);
    }
}
