<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\PromotionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class PromotionRequestController extends Controller
{
    /**
     * Soumettre une demande de passation de promotion.
     * Un étudiant ne peut soumettre qu'une seule demande par année académique.
     */
    public function store(Request $request)
    {
        // 1. Validation des données
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

        $user = $request->user();

        // 2. Récupérer l'année académique active
        $academicYear = AcademicYear::where('status', 'active')->first();

        if (!$academicYear) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune année académique active. Les demandes sont momentanément indisponibles.',
            ], 422);
        }

        // 3. Vérifier la promotion actuelle de l'étudiant
        if (!$user->promotion_id) {
            return response()->json([
                'success' => false,
                'message' => "Votre promotion actuelle est introuvable. Veuillez contacter l'administration.",
            ], 422);
        }

        // 4. Vérifier si l'étudiant a déjà soumis une demande cette année
        $existingRequest = PromotionRequest::where('user_id', $user->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        if ($existingRequest) {
            $messages = [
                'pending' => 'Vous avez déjà une demande en attente de validation pour cette année académique.',
                'approved' => 'Votre demande de passation a déjà été approuvée pour cette année académique.',
                'rejected' => 'Votre demande a déjà été rejetée pour cette année académique. Vous ne pouvez plus soumettre une autre demande cette année.',
            ];

            return response()->json([
                'success' => false,
                'message' => $messages[$existingRequest->status]
                    ?? 'Vous avez déjà soumis une demande pour cette année académique.',
                'status' => $existingRequest->status,
                'academic_year' => $academicYear->name,
            ], 409);
        }

        // 5. Vérifier que la promotion cible est différente de l'actuelle
        if ((int) $validated['target_promotion_id'] === (int) $user->promotion_id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà inscrit dans cette promotion.',
            ], 422);
        }

        // 6. Enregistrer le justificatif
        $path = $request->file('proof_file')->store('proofs', 'public');

        try {
            // 7. Créer la demande
            $promotionRequest = DB::transaction(function () use (
                $user,
                $academicYear,
                $validated,
                $path
            ) {
                return PromotionRequest::create([
                    'user_id' => $user->id,
                    'academic_year_id' => $academicYear->id,
                    'current_promotion_id' => $user->promotion_id,
                    'target_promotion_id' => $validated['target_promotion_id'],
                    'proof_file' => $path,
                    'status' => 'pending',
                ]);
            });

        } catch (QueryException $e) {

            // 8. Gérer une éventuelle double soumission simultanée
            Storage::disk('public')->delete($path);

            $existingRequest = PromotionRequest::where('user_id', $user->id)
                ->where('academic_year_id', $academicYear->id)
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous avez déjà soumis une demande pour cette année académique.',
                    'status' => $existingRequest->status,
                    'academic_year' => $academicYear->name,
                ], 409);
            }

            throw $e;
        }

        // 9. Retourner la réponse
        return response()->json([
            'success' => true,
            'message' => 'Votre demande de passation de promotion a été envoyée avec succès.',
            'data' => $promotionRequest->load([
                'academicYear',
                'currentPromotion',
                'targetPromotion',
            ]),
        ], 201);
    }
}
