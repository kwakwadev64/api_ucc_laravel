<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecoursRequest;
use App\Models\AppealReason;
use App\Models\Course;
use App\Models\Recours;
use App\Models\RecoursAttachment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class RecoursController extends Controller
{
    /**
     * Relations accessibles à l'étudiant.
     * Seule la décision du jury est chargée.
     */
    private const RELATIONS = [
        'academicYear',
        'promotion',
        'courses',
        'reasons',
        'attachments',
        'juryReview:id,recours_id,decision',
    ];

    /**
     * Vérifie si l'étudiant possède un recours actif.
     */
    private function hasActiveRecours(int $userId): bool
    {
        return Recours::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['submitted', 'under_review'])
            ->exists();
    }

    /**
     * OPTIONS DU FORMULAIRE
     */
    public function options(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user &&
            in_array($user->role, ['student', 'cp'], true) &&
            $user->is_active,
            403
        );

        $user->load(['promotion', 'academicYear']);

        if (! $user->promotion || ! $user->academicYear) {
            return response()->json([
                'success' => false,
                'message' => 'Votre promotion ou votre année académique est manquante. Veuillez contacter la faculté.',
            ], 422);
        }

        if (! $user->academicYear->recours_is_open) {
            return response()->json([
                'success' => false,
                'code' => 'RECOURS_CLOSED',
                'message' => 'La période de soumission des recours est actuellement fermée. Veuillez revenir ultérieurement.',
            ], 403);
        }

        $hasActiveRecours = $this->hasActiveRecours($user->id);

        $courses = Course::query()
            ->where('promotion_id', $user->promotion_id)
            ->where('academic_year_id', $user->academic_year_id)
            ->with('teacher:id,first_name,last_name')
            ->orderBy('title')
            ->get()
            ->map(fn (Course $course) => [
                'id' => $course->id,
                'title' => $course->title,
                'professor_name' => trim(
                    ($course->teacher?->first_name ?? '') . ' ' .
                    ($course->teacher?->last_name ?? '')
                ),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,

                'promotion' => [
                    'id' => $user->promotion->id,
                    'name' => $user->promotion->name,
                ],

                'academic_year' => [
                    'id' => $user->academicYear->id,
                    'name' => $user->academicYear->name,
                ],

                'courses' => $courses,

                'reasons' => AppealReason::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                        'code',
                        'requires_description',
                        'requires_attachment',
                    ]),

                'can_submit' => ! $hasActiveRecours,

                'active_recours_message' => $hasActiveRecours
                    ? 'Vous avez déjà un recours soumis ou en cours de traitement. Vous pourrez en soumettre un autre après la décision définitive.'
                    : null,
            ],
        ]);
    }

    /**
     * HISTORIQUE DES RECOURS DE L'ÉTUDIANT CONNECTÉ
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $recours = Recours::query()
            ->where('user_id', $userId)
            ->with(self::RELATIONS)
            ->latest()
            ->get();

        $hasActiveRecours = $this->hasActiveRecours($userId);

        return response()->json([
            'success' => true,

            'can_submit' => ! $hasActiveRecours,

            'message' => $hasActiveRecours
                ? 'Vous avez déjà un recours en cours de traitement.'
                : 'Vous pouvez soumettre un nouveau recours.',

            'data' => $recours->map(
                fn (Recours $record) => $this->serialize($record)
            )->values(),
        ]);
    }

    /**
     * DÉTAIL D'UN RECOURS
     */
    public function show(Request $request, Recours $recours): JsonResponse
    {
        $this->authorizeOwner($request, $recours);

        $recours->load(self::RELATIONS);

        return response()->json([
            'success' => true,
            'data' => $this->serialize($recours),
        ]);
    }

    /**
     * SOUMISSION D'UN RECOURS
     */
    public function store(StoreRecoursRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        $user->loadMissing('academicYear');

        if (! $user->academicYear?->recours_is_open) {
            return response()->json([
                'success' => false,
                'code' => 'RECOURS_CLOSED',
                'message' => 'La période de soumission des recours est actuellement fermée.',
            ], 403);
        }

        $storedPaths = [];

        try {
            $recours = DB::transaction(function () use (
                $validated,
                $request,
                $user,
                &$storedPaths
            ) {
                /*
                 * Verrouillage de la ligne utilisateur
                 * pour éviter les doubles soumissions simultanées.
                 */
                User::query()
                    ->whereKey($user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * Vérification des recours actifs.
                 */
                if ($this->hasActiveRecours($user->id)) {
                    return null;
                }

                /*
                 * Création du recours.
                 */
                $record = Recours::create([
                    'user_id' => $user->id,
                    'academic_year_id' => $validated['academic_year_id'],
                    'promotion_id' => $validated['promotion_id'],
                    'last_name' => trim($validated['last_name']),
                    'first_name' => trim($validated['first_name']),
                    'post_name' => isset($validated['post_name'])
                        ? trim($validated['post_name'])
                        : null,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);

                /*
                 * Enregistrement des cours.
                 */
                foreach ($validated['courses'] as $course) {
                    $record->courses()->attach(
                        $course['course_id'],
                        [
                            'professor_name' => trim(
                                $course['professor_name']
                            ),
                        ]
                    );
                }

                /*
                 * Enregistrement des motifs.
                 */
                foreach ($validated['reasons'] as $reason) {
                    $record->reasons()->attach(
                        $reason['reason_id'],
                        [
                            'description' => $reason['description'] ?? null,
                        ]
                    );
                }

                /*
                 * Enregistrement des pièces justificatives.
                 */
                foreach ($request->file('attachments', []) as $index => $file) {
                    $path = $file->store('recours-private', 'local');

                    if (! $path) {
                        throw new RuntimeException(
                            'Recours attachment storage failed.'
                        );
                    }

                    $storedPaths[] = $path;

                    $record->attachments()->create([
                        'reason_id' => $validated['attachment_reason_ids'][$index],
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }

                return $record->load(self::RELATIONS);
            });

            /*
             * Un recours actif existe déjà.
             */
            if (! $recours) {
                return response()->json([
                    'success' => false,
                    'code' => 'RECOURS_ALREADY_ACTIVE',
                    'message' => 'Vous avez déjà un recours soumis ou en cours de traitement. Vous pourrez en soumettre un autre après la décision définitive.',
                ], 409);
            }

            return response()->json([
                'success' => true,
                'message' => 'Recours enregistré avec succès.',
                'data' => $this->serialize($recours),
            ], 201);

        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);

            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Votre recours n’a pas pu être enregistré. Veuillez réessayer.',
            ], 500);
        }
    }

    /**
     * TÉLÉCHARGEMENT D'UNE PIÈCE JUSTIFICATIVE
     */
    public function download(
        Request $request,
        Recours $recours,
        RecoursAttachment $attachment
    ): StreamedResponse {
        $this->authorizeOwner($request, $recours);

        abort_unless(
            (int) $attachment->recours_id === (int) $recours->id,
            404
        );

        $disk = str_starts_with(
            $attachment->file_path,
            'recours-private/'
        ) ? 'local' : 'public';

        abort_unless(
            Storage::disk($disk)->exists($attachment->file_path),
            404
        );

        $name = basename(
            str_replace('\\', '/', $attachment->original_name)
        );

        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name)
            ?: 'justificatif';

        return Storage::disk($disk)->download(
            $attachment->file_path,
            $name,
            [
                'Content-Type' => 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }

    /**
     * VÉRIFICATION DU PROPRIÉTAIRE
     */
    private function authorizeOwner(
        Request $request,
        Recours $recours
    ): void {
        abort_unless(
            (int) $recours->user_id === (int) $request->user()->id,
            403,
            'Vous ne pouvez pas consulter ce recours.'
        );
    }

    /**
     * SÉRIALISATION DES DONNÉES VISIBLES PAR L'ÉTUDIANT
     *
     * Aucune information interne du jury n'est exposée.
     */
    private function serialize(Recours $recours): array
    {
        $decision = $recours->juryReview?->decision;

        $statusLabel = match ($recours->status) {
            'draft' => 'Brouillon',
            'submitted' => 'Soumis',
            'under_review' => 'En attente',
            'decided' => match ($decision) {
                'founded' => 'Fondé',
                'unfounded' => 'Non fondé',
                default => 'Décidé',
            },
            default => 'Statut inconnu',
        };

        return [
            'id' => $recours->id,

            'academic_year' => [
                'id' => $recours->academicYear?->id,
                'name' => $recours->academicYear?->name,
            ],

            'promotion' => [
                'id' => $recours->promotion?->id,
                'name' => $recours->promotion?->name,
            ],

            'student' => [
                'last_name' => $recours->last_name,
                'post_name' => $recours->post_name,
                'first_name' => $recours->first_name,
            ],

            'status' => $recours->status,
            'status_label' => $statusLabel,

            'submitted_at' => $recours->submitted_at,

            'courses' => $recours->courses
                ->map(fn ($course) => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'professor_name' => $course->pivot->professor_name,
                ])
                ->values(),

            'reasons' => $recours->reasons
                ->map(fn ($reason) => [
                    'id' => $reason->id,
                    'name' => $reason->name,
                    'description' => $reason->pivot->description,
                ])
                ->values(),

            'attachments' => $recours->attachments
                ->map(fn (RecoursAttachment $attachment) => [
                    'id' => $attachment->id,
                    'reason_id' => $attachment->reason_id,
                    'original_name' => $attachment->original_name,
                    'mime_type' => $attachment->mime_type,
                    'size' => $attachment->size,
                    'download_path' => '/recours/' .
                        $recours->id .
                        '/attachments/' .
                        $attachment->id,
                ])
                ->values(),
        ];
    }
}
