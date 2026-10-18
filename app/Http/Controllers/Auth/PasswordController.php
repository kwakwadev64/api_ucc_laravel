<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                'different:current_password',
                Password::min(8),
            ],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'Votre ancien mot de passe est incorrect.',
                ],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Mot de passe modifié avec succès.',
        ]);
    }
}
