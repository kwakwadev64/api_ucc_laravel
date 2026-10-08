<?php

namespace Database\Seeders;

use App\Models\AppealReason;
use Illuminate\Database\Seeder;

class AppealReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            [
                'name' => 'Omission de points',
                'code' => 'missing_points',
                'requires_attachment' => false,
                'requires_description' => false,
            ],
            [
                'name' => 'Calcul inexact de la moyenne de l’épreuve',
                'code' => 'wrong_average',
                'requires_attachment' => false,
                'requires_description' => false,
            ],
            [
                'name' => 'Modification accidentelle de points',
                'code' => 'accidental_modification',
                'requires_attachment' => false,
                'requires_description' => false,
            ],
            [
                'name' => 'Confusion dans l’identité de l’étudiant(e)',
                'code' => 'identity_confusion',
                'requires_attachment' => false,
                'requires_description' => false,
            ],
            [
                'name' => 'Omission du nom de l’étudiant(e)',
                'code' => 'missing_student_name',
                'requires_attachment' => false,
                'requires_description' => false,
            ],
            [
                'name' => 'Pour maladie',
                'code' => 'illness',
                'requires_attachment' => true,
                'requires_description' => false,
            ],
            [
                'name' => 'Non-paiement ou paiement tardif des frais académiques',
                'code' => 'late_payment',
                'requires_attachment' => false,
                'requires_description' => false,
            ],
            [
                'name' => 'Autre motif',
                'code' => 'other',
                'requires_attachment' => false,
                'requires_description' => true,
            ],
        ];

        foreach ($reasons as $reason) {
            AppealReason::updateOrCreate(
                ['code' => $reason['code']],
                $reason
            );
        }
    }
}
