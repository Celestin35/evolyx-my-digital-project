<?php

namespace App\Services;

use App\Models\Sport;
use App\Models\User;

class UserProfileService
{
    public function profileDataFor(User $user): array
    {
        $user->load([
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

        return [
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
                ->orderBy('sort_order')
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
        ];
    }

    public function updatePersonalInfo(User $user, array $data): void
    {
        $user->update([
            'first_name' => $data['first_name'],
            'sex' => $data['sex'],
            'height' => $data['height'],
            'birth_date' => $data['birth_date'],
            'activity_level' => $data['activity_level'],
        ]);

        if (array_key_exists('sport_ids', $data)) {
            $this->syncSports($user, $data['sport_ids']);
        }
    }

    public function syncSports(User $user, array $sportIds): void
    {
        $user->sports()->sync(
            collect($sportIds)->map(fn ($id) => (int) $id)->unique()->values()->all(),
        );
    }

    public function updateAccountInfo(User $user, array $data): void
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }
}
