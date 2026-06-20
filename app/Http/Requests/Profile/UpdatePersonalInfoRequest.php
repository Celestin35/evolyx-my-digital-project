<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonalInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'sex' => ['required', Rule::in(['male', 'female', 'other'])],
            'height' => ['required', 'integer', 'min:50', 'max:300'],
            'birth_date' => ['required', 'date', 'before_or_equal:'.now()->subYears(15)->toDateString()],
            'activity_level' => [
                'required',
                Rule::in(['sedentary', 'light', 'moderate', 'active', 'very_active']),
            ],
            'sport_ids' => ['sometimes', 'array'],
            'sport_ids.*' => ['integer', Rule::exists('sports', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Le nom est requis.',
            'first_name.max' => 'Le nom ne peut pas dÃ©passer 50 caractÃ¨res.',
            'sex.required' => 'Le sexe est requis.',
            'sex.in' => 'Le sexe sÃ©lectionnÃ© est invalide.',
            'height.required' => 'La taille est requise.',
            'height.integer' => 'La taille doit Ãªtre un nombre entier en centimÃ¨tres.',
            'height.min' => 'La taille doit Ãªtre comprise entre 50 et 300 cm.',
            'height.max' => 'La taille doit Ãªtre comprise entre 50 et 300 cm.',
            'activity_level.required' => 'Le niveau d\'activitÃ© est requis.',
            'activity_level.in' => 'Le niveau d\'activitÃ© sÃ©lectionnÃ© est invalide.',
            'birth_date.required' => 'La date de naissance est requise.',
            'birth_date.date' => 'La date de naissance doit Ãªtre une date valide.',
            'birth_date.before_or_equal' => 'Tu dois avoir au moins 15 ans pour utiliser l\'application.',
            'sport_ids.array' => 'La sÃ©lection de sports est invalide.',
            'sport_ids.*.exists' => 'Un sport sÃ©lectionnÃ© est invalide.',
        ];
    }
}
