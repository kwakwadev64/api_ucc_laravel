<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\PromotionRequest;
use App\Models\User;

class PromotionRequestService
{
    public function currentAcademicYear(): ?AcademicYear
    {
        return AcademicYear::where('status', 'active')->first();
    }

    public function findForYear(User $user, AcademicYear $academicYear): ?PromotionRequest
    {
        // Tous les statuts consomment le droit de soumettre pour cette année.
        return PromotionRequest::where('user_id', $user->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();
    }

    /**
     * @return array{academic_year: ?AcademicYear, request: ?PromotionRequest, can_submit: bool, message: string}
     */
    public function currentStatusFor(User $user): array
    {
        $academicYear = $this->currentAcademicYear();
        $existingRequest = $academicYear ? $this->findForYear($user, $academicYear) : null;

        $message = match (true) {
            $academicYear === null => 'Aucune année académique active. Les demandes sont momentanément indisponibles.',
            $existingRequest !== null => $this->alreadySubmittedMessage($existingRequest),
            ! $user->promotion_id => 'Votre promotion actuelle est introuvable. Veuillez contacter l’administration.',
            default => 'Une seule demande de passage est autorisée pour cette année académique.',
        };

        return [
            'academic_year' => $academicYear,
            'request' => $existingRequest,
            'can_submit' => $academicYear !== null && $existingRequest === null && (bool) $user->promotion_id,
            'message' => $message,
        ];
    }

    public function alreadySubmittedMessage(PromotionRequest $request): string
    {
        return match ($request->status) {
            'pending' => 'Vous avez déjà une demande en attente de validation pour cette année académique.',
            'approved' => 'Votre demande de passage a déjà été approuvée pour cette année académique. Vous ne pouvez plus soumettre une autre demande cette année.',
            'rejected' => 'Votre demande a déjà été rejetée pour cette année académique. Vous ne pouvez plus soumettre une autre demande cette année.',
            default => 'Vous avez déjà soumis une demande pour cette année académique.',
        };
    }
}
