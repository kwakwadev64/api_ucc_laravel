<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecoursRequest;
use App\Models\AppealReason;
use App\Models\Course;
use App\Models\Recours;
use App\Models\RecoursAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class RecoursController extends Controller
{
    private const RELATIONS = ['academicYear', 'promotion', 'courses', 'reasons', 'attachments'];

    public function options(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['student', 'cp'], true) && $user->is_active, 403);
        $user->load(['promotion', 'academicYear']);
        if (! $user->promotion || ! $user->academicYear) {
            return response()->json([
                'success' => false,
                'message' => 'Votre promotion ou votre année académique est manquante. Veuillez contacter la faculté.',
            ], 422);
        }

        $courses = Course::query()
            ->where('promotion_id', $user->promotion_id)
            ->where('academic_year_id', $user->academic_year_id)
            ->with('teacher:id,first_name,last_name')
            ->orderBy('title')->get()
            ->map(fn (Course $course) => [
                'id' => $course->id,
                'title' => $course->title,
                'professor_name' => trim(($course->teacher?->first_name ?? '').' '.($course->teacher?->last_name ?? '')),
            ]);

        return response()->json(['success' => true, 'data' => [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'promotion' => ['id' => $user->promotion->id, 'name' => $user->promotion->name],
            'academic_year' => ['id' => $user->academicYear->id, 'name' => $user->academicYear->name],
            'courses' => $courses,
            'reasons' => AppealReason::query()->where('is_active', true)->orderBy('name')
                ->get(['id', 'name', 'code', 'requires_description', 'requires_attachment']),
        ]]);
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' =>
            Recours::query()->where('user_id', $request->user()->id)
                ->with(self::RELATIONS)->latest()->get()->map(fn (Recours $record) => $this->serialize($record)),
        ]);
    }

    public function show(Request $request, Recours $recours): JsonResponse
    {
        $this->authorizeOwner($request, $recours);
        return response()->json(['success' => true, 'data' => $this->serialize($recours->load(self::RELATIONS))]);
    }

    public function store(StoreRecoursRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $storedPaths = [];
        try {
            $recours = DB::transaction(function () use ($validated, $request, &$storedPaths) {
                $record = Recours::create([
                    'user_id' => $request->user()->id,
                    'academic_year_id' => $validated['academic_year_id'],
                    'promotion_id' => $validated['promotion_id'],
                    'last_name' => trim($validated['last_name']),
                    'first_name' => trim($validated['first_name']),
                    'post_name' => isset($validated['post_name']) ? trim($validated['post_name']) : null,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
                foreach ($validated['courses'] as $course) {
                    $record->courses()->attach($course['course_id'], ['professor_name' => trim($course['professor_name'])]);
                }
                foreach ($validated['reasons'] as $reason) {
                    $record->reasons()->attach($reason['reason_id'], ['description' => $reason['description'] ?? null]);
                }
                foreach ($request->file('attachments', []) as $index => $file) {
                    $path = $file->store('recours-private', 'local');
                    if (! $path) throw new RuntimeException('Recours attachment storage failed.');
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
            return response()->json([
                'success' => true, 'message' => 'Recours enregistré avec succès.',
                'data' => $this->serialize($recours),
            ], 201);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            // Ne pas journaliser le formulaire ou les pièces de l'étudiant.
            return response()->json([
                'success' => false,
                'message' => 'Votre recours n’a pas pu être enregistré. Veuillez réessayer.',
            ], 500);
        }
    }

    public function download(Request $request, Recours $recours, RecoursAttachment $attachment): StreamedResponse
    {
        $this->authorizeOwner($request, $recours);
        abort_unless((int) $attachment->recours_id === (int) $recours->id, 404);
        // Compatibilité avec les anciennes pièces, sans déplacement ni suppression.
        $disk = str_starts_with($attachment->file_path, 'recours-private/') ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($attachment->file_path), 404);
        $name = basename(str_replace('\\', '/', $attachment->original_name));
        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?: 'justificatif';
        return Storage::disk($disk)->download($attachment->file_path, $name, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function authorizeOwner(Request $request, Recours $recours): void
    {
        abort_unless((int) $recours->user_id === (int) $request->user()->id, 403, 'Vous ne pouvez pas consulter ce recours.');
    }

    private function serialize(Recours $recours): array
    {
        $data = $recours->toArray();
        $data['attachments'] = $recours->attachments->map(fn (RecoursAttachment $attachment) => [
            'id' => $attachment->id,
            'reason_id' => $attachment->reason_id,
            'original_name' => $attachment->original_name,
            'mime_type' => $attachment->mime_type,
            'size' => $attachment->size,
            'download_path' => '/recours/'.$recours->id.'/attachments/'.$attachment->id,
        ])->all();
        return $data;
    }
}
