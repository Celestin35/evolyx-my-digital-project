<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Role;
use App\Models\User;
use App\Models\WeightEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
            'height' => ['required', 'integer', 'min:100', 'max:250'],
            'weight' => ['required', 'numeric', 'min:20', 'max:500'],
            'activity_level' => ['required', 'in:sedentary,light,moderate,active,very_active'],
            'birth_date' => ['required', 'date', 'before:today'],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'first_name' => $input['first_name'],
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

            return $user;
        });
    }
}
