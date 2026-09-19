<?php

namespace App\Http\Controllers\Api;

use App\Models\Archive;
use App\Services\ArchiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class ArchiveController extends Controller
{
    public function __construct(
        private ArchiveService $archiveService
    ) {
    }

    /**
     * Liste des archives.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'promotion',
            'semester',
            'year',
            'type',
            'name',
        ]);

        $archives = $this->archiveService
            ->getArchives($filters);

        return response()->json([
            'success' => true,
            'data' => $archives->map(
                fn (Archive $archive) => $this->formatArchive($archive)
            ),
        ]);
    }

    /**
     * Afficher une archive précise.
     */
    public function show(Archive $archive): JsonResponse
    {
        $archive = $this->archiveService
            ->getArchive($archive);

        return response()->json([
            'success' => true,
            'data' => $this->formatArchive($archive),
        ]);
    }

    /**
     * Formater une archive pour l'API.
     */
    private function formatArchive(
        Archive $archive
    ): array {
        return [
            'id' => $archive->id,

            'name' => $archive->name,

            'promotion' => $archive->promotion
                ? [
                    'id' => $archive->promotion->id,
                    'name' => $archive->promotion->name,
                ]
                : null,

            'semester' => $archive->semester,

            'year' => $archive->year,

            'type' => [
                'value' => $archive->type,
                'label' => $this->getTypeLabel(
                    $archive->type
                ),
            ],

            'file_url' => $archive->file_path
                ? asset(
                    'storage/' . $archive->file_path
                )
                : null,

            'file_type' => $archive->file_type,

            'uploaded_by' => $archive->uploader
                ? [
                    'id' => $archive->uploader->id,
                    'name' => trim(
                        $archive->uploader->first_name
                        . ' ' .
                        $archive->uploader->last_name
                    ),
                ]
                : null,

            'created_at' => $archive->created_at,

            'updated_at' => $archive->updated_at,
        ];
    }

    /**
     * Libellés des types d'archives.
     */
    private function getTypeLabel(
        string $type
    ): string {
        return match ($type) {
            'exam' => 'Anciens examens',
            'td_tp' => 'TD / TP',
            'interro' => 'Interrogations',
            default => $type,
        };
    }
}
