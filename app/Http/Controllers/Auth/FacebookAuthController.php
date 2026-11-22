<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class FacebookAuthController extends Controller
{
    /**
     * Rediriger l'utilisateur vers Facebook.
     */
    public function redirect()
    {
        return Socialite::driver('facebook')
    ->with(['auth_type' => 'reauthenticate'])
    ->redirect();
    }

    /**
     * Recevoir et traiter la réponse de Facebook.
     */
    public function callback()
    {
        try {
            // Récupérer l'utilisateur authentifié auprès de Facebook.
            $facebookUser = Socialite::driver('facebook')->user();

            $email = $facebookUser->getEmail();

            // Facebook peut ne pas fournir l'adresse email.
            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facebook n’a fourni aucune adresse email. Veuillez utiliser une autre méthode de connexion.',
                ], 422);
            }

            // Récupérer les informations du profil.
            $facebookData = [
                'facebook_id' => $facebookUser->getId(),
                'email' => $email,
                'first_name' => $facebookUser->user['first_name'] ?? null,
                'last_name' => $facebookUser->user['last_name'] ?? null,
                'profile_photo' => $facebookUser->getAvatar(),
            ];

            // Générer un code temporaire aléatoire.
            $code = Str::random(64);

            // Stocker les données pendant 5 minutes.
            Cache::put(
                'facebook_oauth_code:' . hash('sha256', $code),
                $facebookData,
                now()->addMinutes(5)
            );

            // Retourner le code temporaire au client.
            return response()->json([
                'success' => true,
                'message' => 'Authentification Facebook réussie. Vous pouvez poursuivre.',
                'authorization_code' => $code,
                'expires_in' => 300,
                'facebook' => [
                    'email' => $facebookData['email'],
                    'first_name' => $facebookData['first_name'],
                    'last_name' => $facebookData['last_name'],
                    'profile_photo' => $facebookData['profile_photo'],
                ],
            ]);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de récupérer les informations Facebook.',

                'debug' => config('app.debug') ? [
                    'exception' => get_class($e),
                    'error' => $e->getMessage(),
                ] : null,

            ], 500);
        }
    }
}
