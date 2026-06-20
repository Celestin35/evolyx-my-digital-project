<?php

namespace App\Policies;

use App\Models\PerformedSession;
use App\Models\User;

class PerformedSessionPolicy
{
    public function complete(User $user, PerformedSession $performedSession): bool
    {
        return $performedSession->user_id === $user->id;
    }

    public function share(User $user, PerformedSession $performedSession): bool
    {
        return $performedSession->user_id === $user->id;
    }
}
