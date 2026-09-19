<?php

namespace App\Services;

use App\Models\Archive;
use Illuminate\Database\Eloquent\Collection;

class ArchiveService
{
    /**
     * Récupérer toutes les archives avec filtres optionnels.
     */
    public function getArchives(array $filters = []): Collection
    {
        $query = Archive::with([
            'promotion',
            'uploader',
        ]);

        // Filtrer par promotion : ?promotion=L1
        if (!empty($filters['promotion'])) {
            $query->whereHas('promotion', function ($q) use ($filters) {
                $q->where('name', $filters['promotion']);
            });
        }

        // Filtrer par semestre : ?semester=S1
        if (!empty($filters['semester'])) {
            $query->where(
                'semester',
                $filters['semester']
            );
        }

        // Filtrer par année : ?year=2025
        if (!empty($filters['year'])) {
            $query->where(
                'year',
                $filters['year']
            );
        }

        // Filtrer par type : ?type=exam
        if (!empty($filters['type'])) {
            $query->where(
                'type',
                $filters['type']
            );
        }

        // Rechercher par nom du cours : ?name=algorithmique
        if (!empty($filters['name'])) {
            $query->where(
                'name',
                'like',
                '%' . $filters['name'] . '%'
            );
        }

        return $query
            ->latest()
            ->get();
    }

    /**
     * Récupérer une archive précise.
     */
    public function getArchive(Archive $archive): Archive
    {
        return $archive->load([
            'promotion',
            'uploader',
        ]);
    }
}
