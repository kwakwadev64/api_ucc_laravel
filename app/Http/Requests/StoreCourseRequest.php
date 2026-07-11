<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
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
                'required',
                'string',
                'max:255'
            ],

            'description' => [
                'nullable',
                'string'
            ],


            'promotion_id' => [
                'required',
                'exists:promotions,id'
            ],


            'academic_year_id' => [
                'required',
                'exists:academic_years,id'
            ],


            'file' => [
                'required',
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
