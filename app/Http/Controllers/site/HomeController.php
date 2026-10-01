<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // Renvoyer toutes les actualités et les compteurs de la page d'accueil.
    public function getHomeData()
    {
        $actualites = \App\Models\Actualite::orderBy('created_at', 'desc')->get();
        $coursCount = \App\Models\Course::count();
        $photosCount = \App\Models\PhotoFamille::count();
        $batsCount = \App\Models\Schedule::count();

        return response()->json([
            'actualites' => $actualites,
            'cours_count' => $coursCount,
            'photos_count' => $photosCount,
            'bats_count' => $batsCount,
        ]);
    }
}
