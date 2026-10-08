<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecoursRequest;
use App\Models\AppealReason;
use App\Models\Course;
use App\Models\Recours;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class RecoursController extends Controller
{
    /**
     * Créer un recours.
     */
    public function store(StoreRecoursRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $validated = $request->validated();

            /*
             * Vérifier que la promotion sélectionnée
             * correspond bien à l'étudiant connecté.
             */
            if (
                $user->promotion_id === null ||
                (int) $user->promotion_id !== (int) $validated['promotion_id']
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'La promotion sélectionnée ne correspond pas à votre promotion.',
                ], 422);
            }

            /*
             * Vérifier que tous les cours appartiennent
             * à la promotion sélectionnée.
             */
            $courseIds = collect($validated['courses'])
                ->pluck('course_id')
                ->unique()
                ->values();

            $validCourseCount = Course::query()
                ->whereIn('id', $courseIds)
                ->where('promotion_id', $validated['promotion_id'])
                ->count();

            if ($validCourseCount !== $courseIds->count()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un ou plusieurs cours n’appartiennent pas à la promotion sélectionnée.',
                ], 422);
            }

            /*
             * Vérifier que les motifs existent et récupérer
             * leurs règles.
             */
            $reasonIds = collect($validated['reasons'])
                ->pluck('reason_id')
                ->unique()
                ->values();

            $reasons = AppealReason::query()
                ->whereIn('id', $reasonIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            if ($reasons->count() !== $reasonIds->count()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un ou plusieurs motifs sélectionnés sont invalides.',
                ], 422);
            }

            /*
             * Vérifier les descriptions obligatoires.
             */
            foreach ($validated['reasons'] as $reasonData) {
                $reason = $reasons->get($reasonData['reason_id']);

                if (
                    $reason->requires_description &&
                    empty(trim($reasonData['description'] ?? ''))
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => "Une description est obligatoire pour le motif : {$reason->name}.",
                    ], 422);
                }
            }

            /*
             * Vérifier les pièces justificatives obligatoires.
             *
             * attachments[0] correspond à
             * attachment_reason_ids[0].
             */
            $attachments = $request->file('attachments', []);
            $attachmentReasonIds = $request->input('attachment_reason_ids', []);

            foreach ($reasons as $reason) {
                if (! $reason->requires_attachment) {
                    continue;
                }

                $hasAttachment = false;

                foreach ($attachmentReasonIds as $index => $reasonId) {
                    if (
                        (int) $reasonId === (int) $reason->id &&
                        isset($attachments[$index])
                    ) {
                        $hasAttachment = true;
                        break;
                    }
                }

                if (! $hasAttachment) {
                    return response()->json([
                        'success' => false,
                        'message' => "Une pièce justificative est obligatoire pour le motif : {$reason->name}.",
                    ], 422);
                }
            }

            /*
             * Création du recours et enregistrement
             * des relations et des pièces jointes.
             */
            $recours = DB::transaction(function () use (
                $validated,
                $user,
                $attachments,
                $attachmentReasonIds
            ) {
                $recours = Recours::create([
                    'user_id' => $user->id,
                    'academic_year_id' => $validated['academic_year_id'],
                    'promotion_id' => $validated['promotion_id'],
                    'last_name' => $validated['last_name'],
                    'post_name' => $validated['post_name'] ?? null,
                    'first_name' => $validated['first_name'],
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);

                /*
                 * Enregistrer les cours concernés.
                 */
                foreach ($validated['courses'] as $course) {
                    $recours->courses()->attach(
                        $course['course_id'],
                        [
                            'professor_name' => $course['professor_name'],
                        ]
                    );
                }

                /*
                 * Enregistrer les motifs.
                 */
                foreach ($validated['reasons'] as $reason) {
                    $recours->reasons()->attach(
                        $reason['reason_id'],
                        [
                            'description' => $reason['description'] ?? null,
                        ]
                    );
                }

                /*
                 * Enregistrer les pièces justificatives.
                 */
                foreach ($attachments as $index => $file) {
                    $reasonId = $attachmentReasonIds[$index] ?? null;

                    $path = $file->store('recours', 'public');

                    $recours->attachments()->create([
                        'reason_id' => $reasonId,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }

                return $recours;
            });

            return response()->json([
                'success' => true,
                'message' => 'Recours enregistré avec succès.',
                'data' => $recours->load([
                    'academicYear',
                    'promotion',
                    'courses',
                    'reasons',
                    'attachments',
                ]),
            ], 201);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l’enregistrement du recours.',
            ], 500);
        }
    }


    /**
 * Liste des recours de l'étudiant connecté.
 */
public function index(Request $request): JsonResponse
{
    $user = $request->user();

    $recours = Recours::query()
        ->where('user_id', $user->id)
        ->with([
            'academicYear',
            'promotion',
            'courses',
            'reasons',
            'attachments',
        ])
        ->latest()
        ->get();

    return response()->json([
        'success' => true,
        'data' => $recours,
    ]);
}

/**
 * Afficher un recours précis de l'étudiant connecté.
 */
public function show(Request $request, Recours $recours): JsonResponse
{
    $user = $request->user();

    if ((int) $recours->user_id !== (int) $user->id) {
        return response()->json([
            'success' => false,
            'message' => 'Vous n’êtes pas autorisé à consulter ce recours.',
        ], 403);
    }

    $recours->load([
        'academicYear',
        'promotion',
        'courses',
        'reasons',
        'attachments',
    ]);

    return response()->json([
        'success' => true,
        'data' => $recours,
    ]);
}
}
