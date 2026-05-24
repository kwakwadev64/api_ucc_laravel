<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use App\Models\PhotoFamille;
use App\Models\Book;
use App\Models\Actualite; 
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    
    public function getHomeData(): JsonResponse
    {
        try {
            
            return response()->json([
                'actualites'     => Actualite::where('is_published', true)->latest()->get(),
                'photos_famille' => PhotoFamille::where('is_active', true)->latest()->get(),
                'books'          => Book::latest()->take(10)->get()
            ], 200);

        } catch (\Exception $e) {
            
            Log::error('Erreur UCCHUB API HomeData: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'error_details' => $e->getMessage(),
                'suggestion' => 'Vérifie si les colonnes is_active ou les noms de tables existent en BDD.'
            ], 500);
        }
    }
}