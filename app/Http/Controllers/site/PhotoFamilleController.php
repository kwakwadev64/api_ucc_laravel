<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use App\Models\PhotoFamille;
use Illuminate\Http\Request;

class PhotoFamilleController extends Controller
{
    public function index()
    {
        // 1. Récupérer les photos actives
        $photos = PhotoFamille::where('is_active', true)->get();

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

        // 2. Grouper par promotion et formater
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

        return response()->json([
            'success' => true,
            'data' => $albums
        ], 200);
    }
}