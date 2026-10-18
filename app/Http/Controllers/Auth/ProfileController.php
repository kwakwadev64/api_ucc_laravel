<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'last_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'academic_year_id' => [
                'sometimes',
                'nullable',
                'exists:academic_years,id',
            ],

            'profile_photo' => [
                'sometimes',
                'nullable',
                'image',
                'max:2048',
            ],
        ]);

        if ($request->hasFile('profile_photo')) {
            $oldPhoto = $user->profile_photo;

            $newPhoto = $request->file('profile_photo')
                ->store('profile-photos', 'public');

            $validated['profile_photo'] = $newPhoto;

            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }

        $user->fill($validated);
        $user->save();

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => $user->fresh(),
        ]);
    }
}
