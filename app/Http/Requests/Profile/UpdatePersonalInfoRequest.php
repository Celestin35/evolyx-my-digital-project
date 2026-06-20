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
            'first_name.max' => 'Le nom ne peut pas dépasser 50 caractères.',
            'sex.required' => 'Le sexe est requis.',
            'sex.in' => 'Le sexe sélectionné est invalide.',
            'height.required' => 'La taille est requise.',
            'height.integer' => 'La taille doit être un nombre entier en centimètres.',
            'height.min' => 'La taille doit être comprise entre 50 et 300 cm.',
            'height.max' => 'La taille doit être comprise entre 50 et 300 cm.',
            'activity_level.required' => 'Le niveau d\'activité est requis.',
            'activity_level.in' => 'Le niveau d\'activité sélectionné est invalide.',
            'birth_date.required' => 'La date de naissance est requise.',
            'birth_date.date' => 'La date de naissance doit être une date valide.',
            'birth_date.before_or_equal' => 'Tu dois avoir au moins 15 ans pour utiliser l\'application.',
            'sport_ids.array' => 'La sélection de sports est invalide.',
            'sport_ids.*.exists' => 'Un sport sélectionné est invalide.',
        ];
    }
}
