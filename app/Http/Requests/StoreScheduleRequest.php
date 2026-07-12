<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    /**
     * Autorisation de la requête.
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        return [

            'faculty_id' => [
                'required',
                'exists:faculties,id'
            ],


            'promotion_id' => [
                'nullable',
                'exists:promotions,id'
            ],


            'program_id' => [
                'nullable',
                'exists:programs,id'
            ],


            'academic_year_id' => [
                'required',
                'exists:academic_years,id'
            ],


            'title' => [
                'required',
                'string',
                'max:255'
            ],


            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,xlsx',
                'max:10240'
            ],

        ];
    }
}
