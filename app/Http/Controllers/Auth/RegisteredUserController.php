<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterUserRequest $request): JsonResponse
    {
        $academicYearId = $request->academic_year_id 
        ?? AcademicYear::where('status', 'active')->first()?->id;

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'role' => $request->role,
            'faculty_id' => $request->faculty_id,
            'promotion_id' => $request->promotion_id,
            'profile_photo' => $request->profile_photo,
            'academic_year_id' => $academicYearId,
            'is_active' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
        ], 201);
    }
}
