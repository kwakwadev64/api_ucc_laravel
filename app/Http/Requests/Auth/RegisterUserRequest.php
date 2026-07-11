<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    /**
     * Autoriser la requête
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [

            'first_name' => [
                'required',
                'string',
                'max:255',
            ],

            'last_name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                'unique:users,phone',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],

            'role' => [
                'required',
                'string',
                'in:student,cp, teacher,faculty_admin,super_admin',

            ],

            'faculty_id' => [
                'required',
                'exists:faculties,id',
            ],

            'promotion_id' => [
               'required',
                'exists:promotions,id',
            ],
            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],

            'profile_photo' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }


    /**
     * Messages personnalisés
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Cette adresse email est déjà utilisée.',

            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',

            'faculty_id.exists' => 'La faculté sélectionnée est invalide.',

            'promotion_id.exists' => 'La promotion sélectionnée est invalide.',
        ];
    }
}
