<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendStudentChatbotMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'student'
            && $this->user()?->is_active;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:1500'],
        ];
    }
}
