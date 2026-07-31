<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use App\Models\Actualite;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Afficher le détail d'une actualité
     */
    public function show($id)
    {
        $actualite = Actualite::find($id);

        if (!$actualite) {
            return response()->json([
                'message' => 'Actualité introuvable.'
            ], 404);
        }

        // On construit l'URL complète de l'image si c'est un fichier stocké localement
        $imageUrl = $actualite->image_url;
        if ($imageUrl && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $imageUrl = asset('storage/' . $actualite->image_url);
        }

        return response()->json([
            'data' => [
                'id' => $actualite->id,
                'titre' => $actualite->titre,
                'location' => $actualite->location,
                'description' => $actualite->description,
                'image_url' => $imageUrl,
                'rating' => (float) $actualite->rating,
                'filter_type' => $actualite->filter_type,
                'is_published' => $actualite->is_published,
                'created_at' => $actualite->created_at->format('d/m/Y à H:i'),
            ]
        ], 200);
    }
}