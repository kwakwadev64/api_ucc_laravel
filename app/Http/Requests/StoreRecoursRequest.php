<?php

namespace App\Http\Requests;

use App\Models\AppealReason;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRecoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['student', 'cp'], true) && $this->user()->is_active;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'promotion_id' => ['required', 'integer', 'exists:promotions,id'],
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'post_name' => ['nullable', 'string', 'max:100'],
            'courses' => ['required', 'array', 'min:1'],
            'courses.*.course_id' => ['required', 'integer', 'distinct', 'exists:courses,id'],
            'courses.*.professor_name' => ['required', 'string', 'max:150'],
            'reasons' => ['required', 'array', 'min:1'],
            'reasons.*.reason_id' => ['required', 'integer', 'distinct', 'exists:appeal_reasons,id'],
            'reasons.*.description' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,webp'],
            'attachment_reason_ids' => ['nullable', 'array'],
            'attachment_reason_ids.*' => ['required', 'integer', 'exists:appeal_reasons,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Ce champ est obligatoire.',
            'integer' => 'Veuillez sélectionner une valeur valide.',
            'exists' => 'La valeur sélectionnée n’est plus disponible.',
            'string' => 'Veuillez saisir un texte valide.',
            'array' => 'La sélection est invalide.',
            'distinct' => 'Cette valeur a déjà été sélectionnée.',
            'min' => 'Sélectionnez au moins un élément.',
            'max' => 'La valeur dépasse la limite autorisée.',
            'attachments.*.max' => 'Chaque pièce doit faire au maximum 5 Mo.',
            'attachments.*.mimes' => 'Choisissez un PDF ou une image JPG, PNG ou WEBP.',
            'file' => 'Le fichier sélectionné est invalide.',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) return;
            $user = $this->user();
            if (! $user->promotion_id || (int) $this->promotion_id !== (int) $user->promotion_id) {
                $validator->errors()->add('promotion_id', 'Votre promotion doit correspondre à celle de votre profil.');
            }
            if (! $user->academic_year_id || (int) $this->academic_year_id !== (int) $user->academic_year_id) {
                $validator->errors()->add('academic_year_id', 'Votre année académique doit correspondre à celle de votre profil.');
            }
            $courseIds = array_column($this->input('courses'), 'course_id');
            $validCourses = Course::query()->whereIn('id', $courseIds)
                ->where('promotion_id', $user->promotion_id)
                ->where('academic_year_id', $user->academic_year_id)->pluck('id')->all();
            foreach ($this->input('courses') as $index => $course) {
                if (! in_array((int) $course['course_id'], $validCourses, true)) {
                    $validator->errors()->add("courses.$index.course_id", 'Ce cours ne fait pas partie de votre promotion et année.');
                }
                if (trim($course['professor_name']) === '') {
                    $validator->errors()->add("courses.$index.professor_name", 'Le nom du titulaire est obligatoire.');
                }
            }
            $selectedReasons = $this->input('reasons');
            $reasonIds = array_map('intval', array_column($selectedReasons, 'reason_id'));
            $reasons = AppealReason::query()->whereIn('id', $reasonIds)->where('is_active', true)->get()->keyBy('id');
            $files = $this->file('attachments', []);
            $fileReasons = $this->input('attachment_reason_ids', []);
            if (array_keys($files) !== array_keys($fileReasons)) {
                $validator->errors()->add('attachments', 'Chaque pièce doit être associée à un motif.');
            }
            foreach ($fileReasons as $index => $reasonId) {
                if (! in_array((int) $reasonId, $reasonIds, true)) {
                    $validator->errors()->add("attachment_reason_ids.$index", 'La pièce doit correspondre à un motif de ce recours.');
                }
            }
            foreach ($selectedReasons as $index => $item) {
                $reason = $reasons->get($item['reason_id']);
                if (! $reason) {
                    $validator->errors()->add("reasons.$index.reason_id", 'Ce motif n’est plus disponible.');
                    continue;
                }
                if ($reason->requires_description && trim($item['description'] ?? '') === '') {
                    $validator->errors()->add("reasons.$index.description", 'Une explication est obligatoire pour ce motif.');
                }
                if ($reason->requires_attachment && ! collect($fileReasons)->contains(
                    fn ($reasonId, $fileIndex) => (int) $reasonId === (int) $reason->id && isset($files[$fileIndex])
                )) {
                    $validator->errors()->add("reasons.$index.attachment", 'Une pièce justificative est obligatoire pour ce motif.');
                }
            }
        }];
    }
}
