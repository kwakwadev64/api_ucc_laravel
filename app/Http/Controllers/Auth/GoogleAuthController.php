<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Rediriger l'utilisateur vers Google.
     * Demander systématiquement le choix du compte.
     */
    public function redirect()
    {
        return Socialite::driver('google')
            ->with([
                'prompt' => 'select_account',
            ])
            ->redirect();
    }

    /**
     * Recevoir et traiter la réponse de Google.
     */
    public function callback()
    {
        try {
            // Récupérer l'utilisateur authentifié auprès de Google.
            $googleUser = Socialite::driver('google')->user();

            $email = $googleUser->getEmail();

            // Vérifier la présence de l'adresse email.
            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google n’a fourni aucune adresse email.',
                ], 422);
            }

            // Vérifier que Google a confirmé l'adresse email.
            $emailVerified = $googleUser->user['verified_email']
                ?? $googleUser->user['email_verified']
                ?? false;

            if (!$emailVerified) {
                return response()->json([
                    'success' => false,
                    'message' => 'L’adresse email Google n’est pas vérifiée.',
                ], 403);
            }

            // Générer un code temporaire aléatoire.
            $code = Str::random(64);

            // Récupérer les informations du profil Google.
            $googleData = [
                'google_id' => $googleUser->getId(),
                'email' => $email,
                'first_name' => $googleUser->user['given_name'] ?? null,
                'last_name' => $googleUser->user['family_name'] ?? null,
                'profile_photo' => $googleUser->getAvatar(),
            ];

            // Stocker les informations pendant 5 minutes.
            Cache::put(
                'google_oauth_code:' . hash('sha256', $code),
                $googleData,
                now()->addMinutes(5)
            );

            // Retourner le code temporaire au client.
            return response()->json([
                'success' => true,
                'message' => 'Authentification Google réussie. Vous pouvez compléter votre inscription.',
                'authorization_code' => $code,
                'expires_in' => 300,
                'google' => [
                    'email' => $googleData['email'],
                    'first_name' => $googleData['first_name'],
                    'last_name' => $googleData['last_name'],
                    'profile_photo' => $googleData['profile_photo'],
                ],
            ]);

        } catch (\Throwable $e) {

            // Enregistrer l'exception dans storage/logs/laravel.log.
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de récupérer les informations Google.',

                // Diagnostic disponible uniquement en environnement debug.
                'debug' => config('app.debug') ? [
                    'exception' => get_class($e),
                    'error' => $e->getMessage(),
                ] : null,

            ], 500);
        }
    }
}
