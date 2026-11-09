<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromotionRequest;
use Illuminate\Http\Request;

class PromotionRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'target_promotion_id' => 'required|exists:promotions,id',
            'proof_file' => 'required|image|max:4096', // Accepte une image jusqu'à 4 Mo
        ]);

        $user = $request->user();

        // Empêcher d'avoir une double demande en attente
        $existingPending = PromotionRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            return response()->json([
                'message' => 'Vous avez déjà une demande de promotion en attente de validation.'
            ], 422);
        }

        // Stockage du fichier de preuve
        $path = $request->file('proof_file')->store('proofs', 'public');

        $promotionRequest = PromotionRequest::create([
            'user_id' => $user->id,
            'current_promotion_id' => $user->promotion_id,
            'target_promotion_id' => $request->target_promotion_id,
            'proof_file' => $path,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Demande de promotion envoyée avec succès.',
            'data' => $promotionRequest
        ], 201);
    } 
}