<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function download($id)
    {
        // 1. Récupère le chemin du fichier (Exemple dans storage/app/public/photos)
        // Adapte selon ta logique (ex: Photo::findOrFail($id)->path)
        $filePath = "photos/{$id}.jpg"; 

        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json(['message' => 'Image non trouvée.'], 404);
        }

        $fullPath = Storage::disk('public')->path($filePath);

        // 2. Renvoie le fichier en téléchargement avec l'en-tête CORS 'Access-Control-Expose-Headers'
        // C'est Indispensable pour que React/Axios puisse lire le nom du fichier !
        return response()->download($fullPath, "photo-{$id}.jpg", [
            'Access-Control-Expose-Headers' => 'Content-Disposition, Content-Type',
        ]);
    }
}