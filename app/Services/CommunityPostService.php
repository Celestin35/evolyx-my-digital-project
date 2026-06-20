<?php

namespace App\Services;

use App\Models\CommunityPost;
use App\Models\PerformedSession;
use App\Models\User;

class CommunityPostService
{
    public function share(User $user, PerformedSession $performedSession, array $data, bool $hasTitle, bool $hasContent): ?array
    {
        if ($performedSession->completed_at === null) {
            return ['community' => 'Vous pouvez partager uniquement une sÃ©ance validÃ©e.'];
        }

        if ($performedSession->communityPost !== null) {
            return ['community' => 'Cette sÃ©ance est dÃ©jÃ  partagÃ©e.'];
        }

        CommunityPost::query()->create([
            'user_id' => $user->id,
            'performed_session_id' => $performedSession->id,
            'title' => $hasTitle ? $data['title'] : null,
            'content' => $hasContent ? $data['content'] : null,
            'published_at' => now(),
        ]);

        return null;
    }

    public function update(User $user, CommunityPost $communityPost, array $data, bool $hasTitle, bool $hasContent): void
    {
        $communityPost->update([
            'title' => $hasTitle ? $data['title'] : null,
            'content' => $hasContent ? $data['content'] : null,
        ]);
    }

    public function delete(User $user, CommunityPost $communityPost): void
    {
        $communityPost->delete();
    }

    public function follow(User $currentUser, User $user): ?array
    {
        if ($currentUser->id === $user->id) {
            return ['community' => 'Vous ne pouvez pas vous suivre vous-mÃªme.'];
        }

        $currentUser->following()->syncWithoutDetaching([$user->id]);

        return null;
    }

    public function unfollow(User $currentUser, User $user): void
    {
        $currentUser->following()->detach($user->id);
    }
}
