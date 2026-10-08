<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecoursRequest extends FormRequest
{
    /**
     * Autoriser la requête.
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
            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],

            'promotion_id' => [
                'required',
                'exists:promotions,id',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'post_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            /*
             * Plusieurs cours peuvent être concernés.
             */
            'courses' => [
                'required',
                'array',
                'min:1',
            ],

            'courses.*.course_id' => [
                'required',
                'integer',
                'exists:courses,id',
            ],

            'courses.*.professor_name' => [
                'required',
                'string',
                'max:150',
            ],

            /*
             * Plusieurs motifs peuvent être sélectionnés.
             */
            'reasons' => [
                'required',
                'array',
                'min:1',
            ],

            'reasons.*.reason_id' => [
                'required',
                'integer',
                'exists:appeal_reasons,id',
            ],

            'reasons.*.description' => [
                'nullable',
                'string',
            ],

            /*
             * Pièces justificatives.
             *
             * attachments[0] correspond à
             * attachment_reason_ids[0].
             */
            'attachments' => [
                'nullable',
                'array',
            ],

            'attachments.*' => [
                'file',
                'max:5120',
            ],

            'attachment_reason_ids' => [
                'nullable',
                'array',
            ],

            'attachment_reason_ids.*' => [
                'required',
                'integer',
                'exists:appeal_reasons,id',
            ],
        ];
    }
}
