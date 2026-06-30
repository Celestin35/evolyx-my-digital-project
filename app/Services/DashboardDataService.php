<?php

namespace App\Services;

use App\Models\CommunityPost;
use App\Models\Performance;
use App\Models\PerformedSession;
use App\Models\User;

class DashboardDataService
{
    public function __construct(
        private readonly WeightEntriesService $weightEntriesService,
        private readonly FeatureAccessService $featureAccessService,
    ) {}

    public function getForUser(User $user): array
    {
        $canAccessCommunity = $this->featureAccessService->canAccessCommunity($user);

        $recentPerformedSessions = PerformedSession::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->with('workoutSession:id,name')
            ->withCount('performances')
            ->latest('performed_at')
            ->limit(6)
            ->get(['id', 'workout_session_id', 'performed_at', 'completed_at', 'notes']);

        $recentPerformances = Performance::query()
            ->where('user_id', $user->id)
            ->with([
                'exercise:id,name,sport_id',
                'exercise.sport:id,name',
            ])
            ->latest('performed_at')
            ->limit(5)
            ->get([
                'id',
                'performed_at',
                'weight',
                'repetitions',
                'duration_minutes',
                'distance_meters',
                'exercise_id',
            ]);

        $followingIds = $canAccessCommunity
            ? $user->following()->pluck('users.id')
            : collect();

        $communityFeed = $canAccessCommunity
            ? CommunityPost::query()
                ->whereIn('user_id', $followingIds)
                ->with([
                    'user:id,pseudo,first_name',
                    'performedSession.workoutSession:id,name',
                ])
                ->latest('published_at')
                ->latest()
                ->limit(3)
                ->get()
            : collect();

        return [
            'weightEntries' => $this->weightEntriesService->getForUser($user),
            'recentPerformedSessions' => $recentPerformedSessions->map(fn ($session) => [
                'id' => $session->id,
                'workout_session_name' => $session->workoutSession?->name ?? 'Séance',
                'performed_at' => $session->performed_at?->toISOString(),
                'completed_at' => $session->completed_at?->toISOString(),
                'performances_count' => $session->performances_count,
                'notes' => $session->notes,
            ]),
            'recentPerformances' => $recentPerformances->map(fn ($performance) => [
                'id' => $performance->id,
                'performed_at' => $performance->performed_at?->toISOString(),
                'weight' => $performance->weight !== null ? (float) $performance->weight : null,
                'repetitions' => $performance->repetitions,
                'duration_minutes' => $performance->duration_minutes !== null
                    ? (float) $performance->duration_minutes
                    : null,
                'distance_meters' => $performance->distance_meters !== null
                    ? (float) $performance->distance_meters
                    : null,
                'exercise_name' => $performance->exercise?->name ?? 'Exercice',
                'sport_name' => $performance->exercise?->sport?->name,
            ]),
            'canAccessCommunity' => $canAccessCommunity,
            'communityFeed' => $communityFeed->map(fn (CommunityPost $post) => [
                'id' => $post->id,
                'author_name' => $post->user?->pseudo ?? $post->user?->first_name ?? 'Membre Evolyx',
                'title' => $post->title ?? $post->performedSession?->workoutSession?->name ?? 'Séance partagée',
                'workout_session_name' => $post->performedSession?->workoutSession?->name ?? 'Séance',
                'published_at' => $post->published_at?->toISOString(),
            ]),
        ];
    }
}
