<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //renvoyer les données de la page d'accueil du site web(le 3 dernières actualités, nombre de cours, nombre de photo de famille, nombre des bats d'examen)
    public function getHomeData()
    {
        $actualites = \App\Models\Actualite::orderBy('created_at', 'desc')->take(3)->get();
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
