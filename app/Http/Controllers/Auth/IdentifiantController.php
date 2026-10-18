<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Identifiant;
use Illuminate\Http\Request;

class IdentifiantController extends Controller
{
    public function verifier(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $valide = Identifiant::where(
            'code',
            $request->code
        )->exists();

        if (! $valide) {
            return response()->json([
                'message' => 'Code incorrect.',
            ], 422);
        }

        return response()->json([
            'message' => 'Code valide.',
        ]);
    }
}
