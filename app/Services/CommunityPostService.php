<?php

namespace App\Services;

use App\Models\CommunityPost;
use App\Models\PerformedSession;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class CommunityPostService
{
    public function share(User $user, array $data, bool $hasTitle, bool $hasContent): ?array
    {
        $performedSession = PerformedSession::query()
            ->with('communityPost')
            ->where('user_id', $user->id)
            ->find($data['performed_session_id']);

        if (! $performedSession) {
            return ['community' => 'Cette séance ne peut pas être partagée depuis votre compte.'];
        }

        if ($performedSession->completed_at === null) {
            return ['community' => 'Vous pouvez partager uniquement une séance validée.'];
        }

        if ($performedSession->communityPost !== null) {
            return ['community' => 'Cette séance est déjà partagée.'];
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
        $this->authorizeOwner($user, $communityPost);

        $communityPost->update([
            'title' => $hasTitle ? $data['title'] : null,
            'content' => $hasContent ? $data['content'] : null,
        ]);
    }

    public function delete(User $user, CommunityPost $communityPost): void
    {
        $this->authorizeOwner($user, $communityPost);

        $communityPost->delete();
    }

    public function follow(User $currentUser, User $user): ?array
    {
        if ($currentUser->id === $user->id) {
            return ['community' => 'Vous ne pouvez pas vous suivre vous-même.'];
        }

        $currentUser->following()->syncWithoutDetaching([$user->id]);

        return null;
    }

    public function unfollow(User $currentUser, User $user): void
    {
        $currentUser->following()->detach($user->id);
    }

    private function authorizeOwner(User $user, CommunityPost $communityPost): void
    {
        if ($communityPost->user_id !== $user->id) {
            throw new AuthorizationException;
        }
    }
}
