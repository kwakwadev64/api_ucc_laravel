<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{

    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->role !== 'student';
    }


    public function rules(): array
    {
        return [

            'title' => [
                'sometimes',
                'string',
                'max:255'
            ],


            'description' => [
                'nullable',
                'string'
            ],


            'promotion_id' => [
                'sometimes',
                'exists:promotions,id'
            ],


            'academic_year_id' => [
                'sometimes',
                'exists:academic_years,id'
            ],


            'file' => [
                'nullable',
                'file',
                'max:102400',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip'
            ],


            'is_published' => [
                'nullable',
                'boolean'
            ],

        ];
    }
}
