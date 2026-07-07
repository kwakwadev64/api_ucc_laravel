<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{

    public function store(LoginRequest $request)
    {
        $request->validated();


        $login = $request->login;


        $user = User::where('email', $login)
            ->orWhere('phone', $login)
            ->orWhere('matricule', $login)
            ->first();


        if (!$user || !Hash::check($request->password, $user->password)) {

            return response()->json([
                'message' => 'Identifiants incorrects'
            ], 401);

        }


        if (!$user->is_active) {

            return response()->json([
                'message' => 'Votre compte est désactivé'
            ], 403);

        }



        $token = $user->createToken('token')->plainTextToken;


        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();


        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }
}
