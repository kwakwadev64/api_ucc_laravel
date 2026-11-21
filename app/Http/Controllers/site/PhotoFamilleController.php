<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use App\Models\PhotoFamille;
use App\Models\AcademicYear;
use App\Models\Faculty;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PhotoFamilleController extends Controller
{
    public function index(Request $request)
    {
        // 1. Récupération des filtres
        $academicYearId = $request->input('academic_year_id');
        $facultyId = $request->input('faculty_id');
        $promotionId = $request->input('promotion_id');

        // 2. Déterminer l'année académique
        $currentYear = $academicYearId
            ? AcademicYear::find($academicYearId)
            : AcademicYear::where('status', 'active')->first();

        // 3. Construire la requête des photos
        $query = PhotoFamille::with([
            'academicYear',
            'faculty',
            'promotion',
        ])->where('is_active', true);

        // Filtre par année académique
        if ($currentYear) {
            $query->where('academic_year_id', $currentYear->id);
        }

        // Filtre par faculté
        if ($facultyId) {
            $query->where('faculty_id', $facultyId);
        }

        // Filtre par promotion
        if ($promotionId) {
            $query->where('promotion_id', $promotionId);
        }

        // 4. Récupération des photos
        $photos = $query->latest()->get();

        // 5. Fonction de formatage des noms de promotions
        $formatPromotionName = function ($name) {
            $displayNames = [
                'L1' => 'Licence 1',
                'L2' => 'Licence 2',
                'L3' => 'Licence 3',

                'M1RSX' => 'Master 1 Réseaux informatiques',
                'M2RSX' => 'Master 2 Réseaux informatiques',

                'M1CSI' => 'Master 1 Conception des Systèmes d’Information',
                'M2CSI' => 'Master 2 Conception des Systèmes d’Information',
            ];

            return $displayNames[$name] ?? $name;
        };

        // 6. Regroupement des photos par promotion
        $albums = $photos
            ->filter(fn ($photo) => $photo->promotion !== null)
            ->groupBy('promotion_id')
            ->map(function ($groupPhotos, $promotionId) use ($formatPromotionName) {

                $first = $groupPhotos->first();

                $promotion = $first->promotion;
                $faculty = $first->faculty;

                $promotionName = $promotion->name;

                return [
                    'id' => $promotionId,

                    'promotion_id' => $promotionId,
                    'promotion' => $promotionName,
                    'displayName' => $formatPromotionName($promotionName),

                    'faculty_id' => $faculty?->id,
                    'faculty' => $faculty?->name,

                    'mainImage' => $first->image_url,

                    'evenement' => $first->title ?? 'Album ' . $promotionName,
                    'desc' => $first->description ?? '',

                    'images' => $groupPhotos->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'url' => $item->image_url,
                            'title' => $item->title ?? 'Sans titre',
                        ];
                    })->values(),

                    'descriptions' => $groupPhotos->map(function ($item) {
                        return [
                            'desc' => $item->description ?? '',
                        ];
                    })->values(),
                ];
            })
            ->values();

        // 7. Récupération des données pour les filtres
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        $faculties = Faculty::orderBy('name')->get();

        $promotionsQuery = Promotion::query();

        if ($facultyId) {
            $promotionsQuery->where('faculty_id', $facultyId);
        }

        $promotions = $promotionsQuery->orderBy('name')->get();

        // 8. Réponse JSON
        return response()->json([
            'success' => true,

            'current_academic_year' => $currentYear,

            'filters' => [
                'academic_year_id' => $currentYear?->id,
                'faculty_id' => $facultyId,
                'promotion_id' => $promotionId,
            ],

            'academic_years' => $academicYears,
            'faculties' => $faculties,
            'promotions' => $promotions,

            'data' => $albums,
        ], 200);
    }
}
