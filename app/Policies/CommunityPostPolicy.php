<?php

namespace App\Policies;

use App\Models\CommunityPost;
use App\Models\User;

class CommunityPostPolicy
{
    public function update(User $user, CommunityPost $communityPost): bool
    {
        return $communityPost->user_id === $user->id;
    }

    public function delete(User $user, CommunityPost $communityPost): bool
    {
        return $communityPost->user_id === $user->id;
    }
}
