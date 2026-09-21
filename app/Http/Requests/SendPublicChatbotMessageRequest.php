<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendPublicChatbotMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:1000'],
        ];
    }
}
