<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutSession;

class WorkoutSessionPolicy
{
    public function update(User $user, WorkoutSession $workoutSession): bool
    {
        return $workoutSession->user_id === $user->id;
    }

    public function delete(User $user, WorkoutSession $workoutSession): bool
    {
        return $workoutSession->user_id === $user->id;
    }

    public function schedule(User $user, WorkoutSession $workoutSession): bool
    {
        return $workoutSession->user_id === null || $workoutSession->user_id === $user->id;
    }
}
