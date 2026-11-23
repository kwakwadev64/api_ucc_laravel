<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Identifiant;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{


public function exchange(Request $request)
{
    $request->validate([
        'authorization_code' => ['required', 'string', 'max:128'],
    ]);

    $cacheKey = 'google_oauth_code:' . hash(
        'sha256',
        $request->authorization_code
    );

    // Récupérer et consommer le code temporaire.
    $googleData = Cache::pull($cacheKey);

    if (!$googleData) {
        return response()->json([
            'success' => false,
            'message' => 'Code Google invalide, expiré ou déjà utilisé.',
        ], 401);
    }

    // 1. Rechercher un compte déjà associé à ce Google ID.
    $user = User::where('google_id', $googleData['google_id'])
        ->first();

    if ($user) {

        // Vérifier que le compte est actif.
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est désactivé. Veuillez contacter l’administration.',
            ], 403);
        }

        // Générer directement un token Sanctum.
        $token = $user->createToken('ucc-hub-mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion Google réussie.',
            'authenticated' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'matricule' => $user->matricule,
                'role' => $user->role,
                'faculty_id' => $user->faculty_id,
                'promotion_id' => $user->promotion_id,
                'academic_year_id' => $user->academic_year_id,
                'profile_photo' => $user->profile_photo,
            ],
        ]);
    }

    // 2. Vérifier si l'email appartient déjà à un autre compte.
    $existingUser = User::where('email', $googleData['email'])
        ->first();

    if ($existingUser) {
        return response()->json([
            'success' => false,
            'message' => 'Un compte existe déjà avec cette adresse email, mais il n’est pas associé à ce compte Google. Une procédure de liaison est nécessaire.',
        ], 409);
    }

    // 3. Aucun compte existant : autoriser la poursuite de l'inscription.
    $registrationToken = Str::random(64);

    $registrationCacheKey = 'google_registration_token:' . hash(
        'sha256',
        $registrationToken
    );

    Cache::put(
        $registrationCacheKey,
        $googleData,
        now()->addMinutes(10)
    );

    return response()->json([
        'success' => true,
        'message' => 'Authentification Google réussie. Vous pouvez compléter votre inscription.',
        'authenticated' => false,
        'registration_token' => $registrationToken,
        'expires_in' => 600,
        'google' => [
            'email' => $googleData['email'],
            'first_name' => $googleData['first_name'],
            'last_name' => $googleData['last_name'],
            'profile_photo' => $googleData['profile_photo'],
        ],
    ]);
}




public function completeRegistration(Request $request)
{
    // 1. Valider les informations envoyées
    $validated = $request->validate([
        'registration_token' => ['required', 'string', 'max:128'],

        'identifiant' => ['required', 'string'],

        'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],

        'matricule' => ['required', 'string', 'max:20', 'unique:users,matricule'],

        'faculty_id' => ['required', 'integer', 'exists:faculties,id'],

        'promotion_id' => ['required', 'integer', 'exists:promotions,id'],

        'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
    ]);

    // 2. Vérifier le code d'accès UCC
    $validIdentifiant = Identifiant::where(
        'code',
        $validated['identifiant']
    )->exists();

    if (!$validIdentifiant) {
        return response()->json([
            'success' => false,
            'message' => 'Code d’accès UCC invalide.',
        ], 403);
    }

    // 3. Vérifier la cohérence faculté / promotion / année académique
    $validPromotion = Promotion::where('id', $validated['promotion_id'])
        ->where('faculty_id', $validated['faculty_id'])
        ->where('academic_year_id', $validated['academic_year_id'])
        ->exists();

    if (!$validPromotion) {
        return response()->json([
            'success' => false,
            'message' => 'La promotion ne correspond pas à la faculté ou à l’année académique sélectionnée.',
        ], 422);
    }

    // 4. Retrouver les informations Google
    $cacheKey = 'google_registration_token:' . hash(
        'sha256',
        $validated['registration_token']
    );

    $googleData = Cache::get($cacheKey);

    if (!$googleData) {
        return response()->json([
            'success' => false,
            'message' => 'Session d’inscription expirée ou invalide. Veuillez recommencer l’authentification Google.',
        ], 401);
    }

    // 5. Vérifier une nouvelle fois qu'aucun compte n'existe
    $existingUser = User::where('email', $googleData['email'])
        ->orWhere('google_id', $googleData['google_id'])
        ->exists();

    if ($existingUser) {
        return response()->json([
            'success' => false,
            'message' => 'Un compte existe déjà avec ces informations.',
        ], 409);
    }

    // 6. Prénom et nom de repli si Google ne les a pas fournis
    $firstName = trim((string) ($googleData['first_name'] ?? ''));
    $lastName  = trim((string) ($googleData['last_name'] ?? ''));

    if ($firstName === '') {
        $firstName = Str::before($googleData['email'], '@');
    }

    if ($lastName === '') {
        $lastName = $firstName;
    }

    try {
        $user = DB::transaction(function () use ($validated, $googleData, $firstName, $lastName) {

            // Création du compte étudiant
            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $googleData['email'],
                'google_id' => $googleData['google_id'],

                'phone' => $validated['phone'],
                'matricule' => $validated['matricule'],

                'faculty_id' => $validated['faculty_id'],
                'promotion_id' => $validated['promotion_id'],
                'academic_year_id' => $validated['academic_year_id'],

                'profile_photo' => $googleData['profile_photo'],

                // Valeurs contrôlées exclusivement par le backend
                'role' => 'student',
                'is_active' => true,

                // Mot de passe aléatoire, non communiqué au frontend
                'password' => Str::random(64),
            ]);

            // L'adresse Google a déjà été vérifiée
            $user->email_verified_at = now();
            $user->save();

            return $user;
        });

        // Consommer le registration_token après création réussie
        Cache::forget($cacheKey);

        // Générer le token Sanctum
        $token = $user->createToken('ucc-hub-mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inscription terminée avec succès.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'matricule' => $user->matricule,
                'role' => $user->role,
                'faculty_id' => $user->faculty_id,
                'promotion_id' => $user->promotion_id,
                'academic_year_id' => $user->academic_year_id,
                'profile_photo' => $user->profile_photo,
            ],
        ], 201);

    } catch (\Illuminate\Database\QueryException $e) {
        report($e);

        return response()->json([
            'success' => false,
            'message' => 'Une erreur est survenue lors de la création du compte. Vérifiez les informations et réessayez.',
        ], 500);
    }
}


}
