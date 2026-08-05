<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use App\Models\SectionEquipe;
use App\Models\Membre;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class EquipeController extends Controller
{
    public function index(): JsonResponse
    {
        // 1. Récupérer toutes les années distinctes
        $annees = SectionEquipe::distinct()
            ->orderBy('annee', 'desc')
            ->pluck('annee');

        // 2. Charger les sections avec leurs membres associés
        $sectionsData = SectionEquipe::with('membre')->get();

        // 3. Formater la structure JSON pour React
        $equipesParAnnee = [];

        foreach ($sectionsData as $pivot) {
            $annee = $pivot->annee;
            $secId = $pivot->section_id;
            $membre = $pivot->membre;

            if (!isset($equipesParAnnee[$annee])) {
                $equipesParAnnee[$annee] = [];
            }

            if (!isset($equipesParAnnee[$annee][$secId])) {
                $equipesParAnnee[$annee][$secId] = [
                    'id' => $secId,
                    'titre' => $pivot->titre,
                    'description' => $pivot->description,
                    'membres' => []
                ];
            }

            if ($membre) {
                // Formater la photo avec l'URL absolue si stockée dans le storage Laravel
                $photoUrl = null;
    if ($membre->photo) {
        if (str_starts_with($membre->photo, 'http')) {
            $photoUrl = $membre->photo;
        } else {
            // Force l'URL absolue complète
            $photoUrl = asset(Storage::url($membre->photo));
        }
    }

    $equipesParAnnee[$annee][$secId]['membres'][] = [
        'id' => $membre->id,
        'nom' => $membre->nom,
        'role' => $membre->role,
        'description' => $membre->description,
        'photo' => $photoUrl,
        'sujetMemoire' => $membre->sujet_memoire,
        'github' => $membre->github,
        'linkedin' => $membre->linkedin,
        'portfolio' => $membre->portfolio,
        'sources' => []
    ];
            }
        }

        // Convertir les objets associatifs en listes pour React
        $donneesFinales = [];
        foreach ($equipesParAnnee as $annee => $sections) {
            $donneesFinales[$annee] = array_values($sections);
        }

        return response()->json([
            'annees' => $annees,
            'donnees' => $donneesFinales,
        ]);
    }
}