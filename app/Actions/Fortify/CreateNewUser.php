<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'sex' => ['required', 'in:male,female,other'],
            'height' => ['required', 'integer', 'min:50', 'max:300'],
            'weight' => ['required', 'numeric', 'min:20', 'max:600'],
            'activity_level' => ['required', 'in:sedentary,light,moderate,active,very_active'],
            'birth_date' => ['required', 'date', 'before_or_equal:'.now()->subYears(15)->toDateString()],
            'sport_ids' => ['sometimes', 'array'],
            'sport_ids.*' => ['integer', Rule::exists('sports', 'id')],
            'password' => $this->passwordRules(),
        ], [
            ...$this->profileValidationMessages(),
            'sex.required' => 'Le sexe est requis.',
            'sex.in' => 'Le sexe sélectionné est invalide.',
            'height.required' => 'La taille est requise.',
            'height.integer' => 'La taille doit être un nombre entier en centimètres.',
            'height.min' => 'La taille doit être comprise entre 50 et 300 cm.',
            'height.max' => 'La taille doit être comprise entre 50 et 300 cm.',
            'weight.required' => 'Le poids est requis.',
            'weight.numeric' => 'Le poids doit être un nombre.',
            'weight.min' => 'Le poids doit être compris entre 20 et 600 kg.',
            'weight.max' => 'Le poids doit être compris entre 20 et 600 kg.',
            'activity_level.required' => 'Le niveau d\'activité est requis.',
            'activity_level.in' => 'Le niveau d\'activité sélectionné est invalide.',
            'birth_date.required' => 'La date de naissance est requise.',
            'birth_date.date' => 'La date de naissance doit être une date valide.',
            'birth_date.before_or_equal' => 'Tu dois avoir au moins 15 ans pour utiliser l\'application.',
            'sport_ids.array' => 'La sélection de sports est invalide.',
            'sport_ids.*.exists' => 'Un sport sélectionné est invalide.',
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'first_name' => $input['first_name'],
                'pseudo' => $input['pseudo'],
                'email' => $input['email'],
                'password' => $input['password'],
                'sex' => $input['sex'],
                'height' => (int) $input['height'],
                'activity_level' => $input['activity_level'],
                'birth_date' => $input['birth_date'],
                'role_id' => Role::query()->where('name', 'user')->value('id') ?? 1,
            ]);

            $user->weightEntries()->create([
                'weight' => $input['weight'],
            ]);

            $sportIds = collect($input['sport_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if (! empty($sportIds)) {
                $user->sports()->sync($sportIds);
            }

            $freePlanId = SubscriptionPlan::query()
                ->where('name', 'Gratuit')
                ->value('id');

            if ($freePlanId) {
                $user->subscriptions()->create([
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                    'is_active' => true,
                    'subscription_plan_id' => $freePlanId,
                ]);
            }

            return $user;
        });
    }
}
