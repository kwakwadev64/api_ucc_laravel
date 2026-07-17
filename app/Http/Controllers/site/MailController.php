<?php

namespace App\Http\Controllers\site;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 

class MailController extends Controller
{
    public function sendMail(Request $request)
    {
        // 1. Validation des données du formulaire (stockée dans $validatedData)
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // 2. Créer l'enregistrement en utilisant la bonne variable : $validatedData
        $mail = \App\Models\Mail::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'subject' => $validatedData['subject'],
            'message' => $validatedData['message'],
            'lu' => false, // Par défaut le message n'est pas lu
        ]);

        // 3. Retourner la réponse avec la variable $mail désormais bien définie
        return response()->json([
            'message' => 'Mail reçu avec succès.',
            'data' => $mail,
        ], 201);
    }
}