<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user()->load([
            'role',
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
            ],
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
            'height' => ['required', 'integer', 'min:100', 'max:250'],
            'birth_date' => ['required', 'date', 'before:today'],
            'activity_level' => [
                'required',
                Rule::in(['sedentary', 'light', 'moderate', 'active', 'very_active']),
            ],
        ]);

        $request->user()->update($validatedData);

        return to_route('profile')->with(
            'success',
            'Informations personnelles mises a jour avec succes.',
        );
    }

    public function updateAccountInfo(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'pseudo' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'pseudo')->ignore($request->user()->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($request->user()->id),
            ],
        ]);

        $user = $request->user();
        $user->fill($validatedData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('profile')->with(
            'success',
            'Informations du compte mises a jour avec succes.',
        );
    }
}
