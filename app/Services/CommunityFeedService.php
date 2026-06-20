<?php

namespace App\Services;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Collection;

class CommunityFeedService
{
    public function __construct(
        private readonly FeatureAccessService $featureAccessService,
    ) {}

    public function pageDataFor(User $user): array
    {
        $canAccessCommunity = $this->featureAccessService->canAccessCommunity($user);
        $followingIds = $canAccessCommunity
            ? $user->following()->pluck('users.id')
            : collect();

        $followingFeed = $canAccessCommunity
            ? $this->postsQuery()
                ->whereIn('user_id', $followingIds)
                ->limit(50)
                ->get()
                ->map(fn (CommunityPost $post) => $this->formatPost($post, $user->id))
                ->values()
            : collect();

        return [
            'canAccessCommunity' => $canAccessCommunity,
            'activeSubscriptionPlan' => $this->featureAccessService->activePlanName($user),
            'followingFeed' => $followingFeed,
            'ownPosts' => $canAccessCommunity
                ? $this->postsQuery()
                    ->where('user_id', $user->id)
                    ->limit(50)
                    ->get()
                    ->map(fn (CommunityPost $post) => $this->formatPost($post, $user->id))
                    ->values()
                : collect(),
            'following' => $canAccessCommunity
                ? $user->following()
                    ->orderBy('pseudo')
                    ->get(['users.id', 'users.pseudo', 'users.first_name'])
                    ->map(fn (User $followedUser) => $this->formatUserSummary($followedUser, true))
                    ->values()
                : collect(),
            'followers' => $canAccessCommunity
                ? $user->followers()
                    ->orderBy('pseudo')
                    ->get(['users.id', 'users.pseudo', 'users.first_name'])
                    ->map(fn (User $follower) => $this->formatUserSummary(
                        $follower,
                        $followingIds->contains($follower->id),
                    ))
                    ->values()
                : collect(),
            'posts' => $followingFeed,
        ];
    }

    public function searchUsers(User $user, string $search): Collection
    {
        if (mb_strlen($search) < 2) {
            return collect();
        }

        $followingIds = $user->following()->pluck('users.id');

        return User::query()
            ->whereKeyNot($user->id)
            ->where(function ($query) use ($search) {
                $query->where('pseudo', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%");
            })
            ->orderBy('pseudo')
            ->limit(8)
            ->get(['id', 'pseudo', 'first_name'])
            ->map(fn (User $result) => $this->formatUserSummary(
                $result,
                $followingIds->contains($result->id),
            ))
            ->values();
    }

    public function profileFor(User $currentUser, User $user): array
    {
        $user->loadCount(['followers', 'following', 'communityPosts']);

        $posts = $this->postsQuery()
            ->where('user_id', $user->id)
            ->limit(3)
            ->get()
            ->map(fn (CommunityPost $post) => $this->formatPost($post, $currentUser->id))
            ->values();

        return [
            ...$this->formatUserSummary(
                $user,
                $currentUser->following()->whereKey($user->id)->exists(),
            ),
            'followers_count' => $user->followers_count,
            'following_count' => $user->following_count,
            'posts_count' => $user->community_posts_count,
            'posts' => $posts,
        ];
    }

    private function postsQuery()
    {
        return CommunityPost::query()
            ->with([
                'user:id,pseudo,first_name',
                'performedSession.workoutSession:id,name',
                'performedSession.performances.exercise:id,name,sport_id,exercise_category_id',
                'performedSession.performances.exercise.sport:id,name',
                'performedSession.performances.exercise.category:id,name',
                'performedSession.performances.metricValues.metric:id,key,label,unit,value_type',
            ])
            ->latest('published_at')
            ->latest();
    }

    public function formatPost(CommunityPost $post, int $currentUserId): array
    {
        $performedSession = $post->performedSession;

        return [
            'id' => $post->id,
            'author_name' => $post->user?->pseudo ?? $post->user?->first_name ?? 'Membre Evolyx',
            'is_own_post' => $post->user_id === $currentUserId,
            'title' => $post->title,
            'content' => $post->content,
            'published_at' => $post->published_at?->toISOString(),
            'performed_session' => [
                'id' => $performedSession->id,
                'workout_session_name' => $performedSession->workoutSession?->name,
                'performed_at' => $performedSession->performed_at?->toISOString(),
                'completed_at' => $performedSession->completed_at?->toISOString(),
                'notes' => $performedSession->notes,
                'performances' => $performedSession->performances
                    ->map(fn ($performance) => [
                        'exercise_name' => $performance->exercise?->name,
                        'sport_name' => $performance->exercise?->sport?->name,
                        'category_name' => $performance->exercise?->category?->name,
                        'weight' => $performance->weight !== null ? (float) $performance->weight : null,
                        'repetitions' => $performance->repetitions,
                        'duration_minutes' => $performance->duration_minutes !== null
                            ? (float) $performance->duration_minutes
                            : null,
                        'distance_meters' => $performance->distance_meters !== null
                            ? (float) $performance->distance_meters
                            : null,
                        'metric_values' => $performance->metricValues
                            ->map(fn ($metricValue) => [
                                'key' => $metricValue->metric?->key,
                                'label' => $metricValue->metric?->label,
                                'unit' => $metricValue->metric?->unit,
                                'value' => (float) $metricValue->value,
                            ])
                            ->filter(fn ($metricValue) => $metricValue['key'] !== null)
                            ->values(),
                    ])
                    ->values(),
            ],
        ];
    }

    public function formatUserSummary(User $user, bool $isFollowing): array
    {
        $displayName = $user->pseudo ?? $user->first_name ?? 'Membre Evolyx';

        return [
            'id' => $user->id,
            'pseudo' => $user->pseudo,
            'display_name' => $displayName,
            'initial' => mb_strtoupper(mb_substr($displayName, 0, 1)),
            'avatar_color' => $this->avatarColor($user->id),
            'is_following' => $isFollowing,
        ];
    }

    private function avatarColor(int $userId): string
    {
        $colors = [
            '#7a4896',
            '#f47b3a',
            '#0f766e',
            '#2563eb',
            '#be123c',
            '#4d7c0f',
        ];

        return $colors[$userId % count($colors)];
    }
}
