<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use App\Models\PhotoFamille;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class PhotoFamilleController extends Controller
{
    public function index(Request $request)
    {
        // 1. Déterminer l'année académique (soit celle demandée, soit l'active par défaut)
        $academicYearId = $request->input('academic_year_id');

        if ($academicYearId) {
            $currentYear = AcademicYear::find($academicYearId);
        } else {
            $currentYear = AcademicYear::where('status', 'active')->first();
        }

        // 2. Récupérer les photos actives en filtrant par année académique si elle existe
        $query = PhotoFamille::where('is_active', true);

        if ($currentYear) {
            $query->where('academic_year_id', $currentYear->id);
        }

        $photos = $query->get();

        // Noms d'affichage selon la promotion
        $displayNames = [
            'L1' => 'Licence 1',
            'L2' => 'Licence 2',
            'L3' => 'Licence 3',
            'M1 Réseau' => 'Master 1 Réseaux & Télécoms',
            'M2 Réseau' => 'Master 2 Réseaux & Télécoms',
            'M1 Conception' => 'Master 1 Conception',
            'M2 Conception' => 'Master 2 Conception',
        ];

        // 3. Grouper par promotion et formater
        $albums = $photos->groupBy('promotion')->map(function ($groupPhotos, $promotionKey) use ($displayNames) {
            $first = $groupPhotos->first();

            return [
                'id' => $promotionKey,
                'promotion' => $promotionKey,
                'displayName' => $displayNames[$promotionKey] ?? $promotionKey,
                'mainImage' => $first ? $first->image_url : '',
                'evenement' => $first ? $first->title : 'Album ' . $promotionKey,
                'desc' => $first ? $first->description : '',
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
        })->values();

        // 4. Récupérer la liste de toutes les années académiques pour les filtres du front-end
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'current_academic_year' => $currentYear,
            'academic_years' => $academicYears,
            'data' => $albums
        ], 200);
    }
}