<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [

            'faculty_id' => [
                'sometimes',
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
                'sometimes',
                'exists:academic_years,id'
            ],


            'title' => [
                'sometimes',
                'string',
                'max:255'
            ],


            'file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,xlsx',
                'max:10240'
            ],

        ];
    }
}
