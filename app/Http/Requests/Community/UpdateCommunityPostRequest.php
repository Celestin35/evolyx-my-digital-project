<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunityPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:120'],
            'content' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
