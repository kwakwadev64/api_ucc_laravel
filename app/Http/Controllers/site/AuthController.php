<?php
namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Cas 1 : L'e-mail n'existe pas en BDD
        if (!$user) {
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => [
                    'email' => ['Aucun compte trouvé avec cette adresse e-mail.']
                ]
            ], 422);
        }

        // Cas 2 : Le mot de passe ne correspond pas
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => [
                    'password' => ['Le mot de passe saisi est incorrect.']
                ]
            ], 422);
        }

        // Connexion réussie
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => $user
        ], 200);
    }
}