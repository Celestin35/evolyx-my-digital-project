<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'first_name' => $this->firstNameRules(),
            'pseudo' => $this->pseudoRules($userId),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user first names.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function firstNameRules(): array
    {
        return ['required', 'string', 'max:50'];
    }

    /**
     * Get the validation rules used to validate user pseudos.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function pseudoRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'min:3',
            'max:30',
            'regex:/^[A-Za-z0-9_]+$/',
            $userId === null
                ? Rule::unique(User::class, 'pseudo')
                : Rule::unique(User::class, 'pseudo')->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email:rfc',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Get French validation messages for profile/account fields.
     *
     * @return array<string, string>
     */
    protected function profileValidationMessages(): array
    {
        return [
            'first_name.required' => 'Le nom est requis.',
            'first_name.max' => 'Le nom ne peut pas dépasser 50 caractères.',
            'pseudo.required' => 'Le pseudo est requis.',
            'pseudo.min' => 'Le pseudo doit contenir au moins 3 caractères.',
            'pseudo.max' => 'Le pseudo ne peut pas dépasser 30 caractères.',
            'pseudo.regex' => 'Le pseudo ne peut contenir que des lettres, des chiffres et des underscores.',
            'pseudo.unique' => 'Ce pseudo est déjà utilisé.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être une adresse valide.',
            'email.max' => 'L\'email ne peut pas dépasser 255 caractères.',
            'email.unique' => 'Un compte existe déjà avec cet email.',
        ];
    }
}
