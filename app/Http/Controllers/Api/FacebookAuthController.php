<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use App\Models\User;
use App\Models\Identifiant;
use App\Models\Promotion;

class FacebookAuthController extends Controller
{
    /**
     * Échanger le code Facebook contre une session UCC Hub.
     */
    public function exchange(Request $request)
    {
        $validated = $request->validate([
            'authorization_code' => ['required', 'string', 'max:128'],
        ]);

        $cacheKey = 'facebook_oauth_code:' . hash(
            'sha256',
            $validated['authorization_code']
        );

        // Consommer le code temporaire : il ne peut être utilisé qu'une fois.
        $facebookData = Cache::pull($cacheKey);

        if (!$facebookData) {
            return response()->json([
                'success' => false,
                'message' => 'Code Facebook invalide, expiré ou déjà utilisé.',
            ], 401);
        }

        // 1. Rechercher un compte déjà associé à ce Facebook ID.
        $user = User::where('facebook_id', $facebookData['facebook_id'])
            ->first();

        if ($user) {

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre compte est désactivé. Veuillez contacter l’administration.',
                ], 403);
            }

            $token = $user->createToken('ucc-hub-mobile')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Connexion Facebook réussie.',
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

        // 2. Vérifier si l'adresse email appartient déjà à un compte.
        $existingUser = User::where('email', $facebookData['email'])
            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'Un compte existe déjà avec cette adresse email, mais il n’est pas associé à ce compte Facebook. Une procédure de liaison est nécessaire.',
            ], 409);
        }

        // 3. Préparer une nouvelle inscription.
        $registrationToken = Str::random(64);

        $registrationCacheKey = 'facebook_registration_token:' . hash(
            'sha256',
            $registrationToken
        );

        Cache::put(
            $registrationCacheKey,
            $facebookData,
            now()->addMinutes(10)
        );

        return response()->json([
            'success' => true,
            'message' => 'Authentification Facebook réussie. Vous pouvez compléter votre inscription.',
            'authenticated' => false,
            'registration_token' => $registrationToken,
            'expires_in' => 600,
            'facebook' => [
                'email' => $facebookData['email'],
                'first_name' => $facebookData['first_name'] ?? null,
                'last_name' => $facebookData['last_name'] ?? null,
                'profile_photo' => $facebookData['profile_photo'],
            ],
        ]);
    }

    /**
     * Finaliser l'inscription d'un nouvel étudiant.
     */
    public function completeRegistration(Request $request)
    {
        $validated = $request->validate([
            'registration_token' => ['required', 'string', 'max:128'],

            'first_name' => ['required', 'string', 'max:100'],

            'last_name' => ['required', 'string', 'max:100'],

            'identifiant' => ['required', 'string'],

            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],

            'matricule' => ['required', 'string', 'max:20', 'unique:users,matricule'],

            'faculty_id' => ['required', 'integer', 'exists:faculties,id'],

            'promotion_id' => ['required', 'integer', 'exists:promotions,id'],

            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
        ]);

        // 1. Vérifier le code d'accès UCC.
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

        // 2. Vérifier la cohérence faculté / promotion / année académique.
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

        // 3. Récupérer les informations Facebook.
        $cacheKey = 'facebook_registration_token:' . hash(
            'sha256',
            $validated['registration_token']
        );

        $facebookData = Cache::get($cacheKey);

        if (!$facebookData) {
            return response()->json([
                'success' => false,
                'message' => 'Session d’inscription expirée ou invalide. Veuillez recommencer l’authentification Facebook.',
            ], 401);
        }

        // 4. Vérifier une nouvelle fois qu'aucun compte n'existe.
        $existingUser = User::where('email', $facebookData['email'])
            ->orWhere('facebook_id', $facebookData['facebook_id'])
            ->exists();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'Un compte existe déjà avec ces informations.',
            ], 409);
        }

        try {
            $user = DB::transaction(function () use ($validated, $facebookData) {

                $user = User::create([
                    // Informations fournies par le formulaire.
                    'first_name' => trim($validated['first_name']),
                    'last_name' => trim($validated['last_name']),

                    // Informations obtenues depuis Facebook.
                    'email' => $facebookData['email'],
                    'facebook_id' => $facebookData['facebook_id'],
                    'profile_photo' => $facebookData['profile_photo'],

                    // Informations académiques.
                    'phone' => $validated['phone'],
                    'matricule' => $validated['matricule'],
                    'faculty_id' => $validated['faculty_id'],
                    'promotion_id' => $validated['promotion_id'],
                    'academic_year_id' => $validated['academic_year_id'],

                    'role' => 'student',
                    'is_active' => true,

                    'password' => Str::random(64),
                ]);

                // À ne pas considérer comme une preuve de vérification
                // de l'adresse email : Facebook a seulement fourni l'adresse.
                $user->email_verified_at = null;
                $user->save();

                return $user;
            });

            // Consommer le token d'inscription après création réussie.
            Cache::forget($cacheKey);

            // Générer le token Sanctum.
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

        } catch (QueryException $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création du compte. Vérifiez les informations et réessayez.',
            ], 500);
        }
    }
}
