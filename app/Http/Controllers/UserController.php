<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Concerns\ProfileValidationRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use ProfileValidationRules;

    public function show(Request $request): Response
    {
        $user = $request->user()->load([
            'role',
            'sports',
            'latestWeightEntry',
            'goals' => fn ($query) => $query
                ->where('is_active', true)
                ->with('goalType')
                ->latest()
                ->limit(1),
        ]);

        $activeGoal = $user->goals->first();

        return Inertia::render('Profile', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'pseudo' => $user->pseudo,
                'email' => $user->email,
                'sex' => $user->sex,
                'height' => $user->height,
                'birth_date' => $user->birth_date?->toDateString(),
                'activity_level' => $user->activity_level,
                'age' => $user->age,
                'current_weight' => $user->current_weight,
                'role' => $user->role?->name,
                'sport_ids' => $user->sports->pluck('id')->values(),
                'sports' => $user->sports->map(fn ($sport) => [
                    'id' => $sport->id,
                    'name' => $sport->name,
                ])->values(),
            ],
            'availableSports' => Sport::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($sport) => [
                    'id' => $sport->id,
                    'name' => $sport->name,
                ]),
            'activeGoal' => $activeGoal ? [
                'target_weight' => $activeGoal->target_weight,
                'weekly_weight_goal' => $activeGoal->weekly_weight_goal,
                'goal_end_date' => $activeGoal->goal_end_date?->toDateString(),
                'goal_type' => $activeGoal->goalType?->name,
            ] : null,
        ]);
    }

    public function updatePersonalInfo(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
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
        ], [
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
        ]);

        $user = $request->user();
        $user->update([
            'first_name' => $validatedData['first_name'],
            'sex' => $validatedData['sex'],
            'height' => $validatedData['height'],
            'birth_date' => $validatedData['birth_date'],
            'activity_level' => $validatedData['activity_level'],
        ]);
        if (array_key_exists('sport_ids', $validatedData)) {
            $user->sports()->sync(
                collect($validatedData['sport_ids'])->map(fn ($id) => (int) $id)->unique()->values()->all(),
            );
        }

        return to_route('profile')->with(
            'success',
            'Informations personnelles mises à jour avec succès.',
        );
    }

    public function updateSports(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'sport_ids' => ['present', 'array'],
            'sport_ids.*' => ['integer', Rule::exists('sports', 'id')],
        ], [
            'sport_ids.present' => 'La sélection de sports est requise.',
            'sport_ids.array' => 'La sélection de sports est invalide.',
            'sport_ids.*.exists' => 'Un sport sélectionné est invalide.',
        ]);

        $request->user()->sports()->sync(
            collect($validatedData['sport_ids'])->map(fn ($id) => (int) $id)->unique()->values()->all(),
        );

        return to_route('profile')->with(
            'success',
            'Sports pratiqués mis à jour avec succès.',
        );
    }

    public function updateAccountInfo(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'pseudo' => $this->pseudoRules($request->user()->id),
            'email' => $this->emailRules($request->user()->id),
        ], $this->profileValidationMessages());

        $user = $request->user();
        $user->fill($validatedData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('profile')->with(
            'success',
            'Informations du compte mises à jour avec succès.',
        );
    }
}
