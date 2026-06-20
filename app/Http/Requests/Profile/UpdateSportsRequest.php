<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSportsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sport_ids' => ['present', 'array'],
            'sport_ids.*' => ['integer', Rule::exists('sports', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'sport_ids.present' => 'La sÃ©lection de sports est requise.',
            'sport_ids.array' => 'La sÃ©lection de sports est invalide.',
            'sport_ids.*.exists' => 'Un sport sÃ©lectionnÃ© est invalide.',
        ];
    }
}
