<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use Illuminate\Http\Request;

class ActualiteController extends Controller
{
    public function index() {
        $actualites = Actualite::all();
        return response()->json($actualites);
    }
    public function show($id) {
        $actualite = Actualite::find($id);
        if (!$actualite) {
            return response()->json(['message' => 'Introuvable'], 404);
        }
        return response()->json([
        'id'            => $actualite->id,
        'titre'         => $actualite->titre,
        'location'      => $actualite->location,      
        'description'   => $actualite->description,
        'image_url'     => $actualite->image_url,
        'rating'        => (float)$actualite->rating, 
        'filter_type'   => $actualite->filter_type,   
        'is_published'  => (bool)$actualite->is_published,
        'likes_count'   => $actualite->likes_count ?? 0, 
    ], 200);
    }
}
