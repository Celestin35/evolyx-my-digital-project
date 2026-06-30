<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeightEntry;

class WeightEntryPolicy
{
    public function view(User $user, WeightEntry $weightEntry): bool
    {
        return $weightEntry->user_id === $user->id;
    }

    public function update(User $user, WeightEntry $weightEntry): bool
    {
        return $weightEntry->user_id === $user->id;
    }

    public function delete(User $user, WeightEntry $weightEntry): bool
    {
        return $weightEntry->user_id === $user->id;
    }
}
