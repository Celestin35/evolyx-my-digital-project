<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\PerformedSession;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $canAccessCommunity = $user->hasPremiumFeatures();
        $activeSubscriptionPlan = $this->activeSubscriptionPlanName($user);
        $followingIds = $canAccessCommunity
            ? $user->following()->pluck('users.id')
            : collect();

        $followingFeed = $canAccessCommunity
            ? CommunityPost::query()
                ->whereIn('user_id', $followingIds)
                ->with([
                    'user:id,pseudo,first_name',
                    'performedSession.workoutSession:id,name',
                    'performedSession.performances.exercise:id,name,sport_id,exercise_category_id',
                    'performedSession.performances.exercise.sport:id,name',
                    'performedSession.performances.exercise.category:id,name',
                    'performedSession.performances.metricValues.metric:id,key,label,unit,value_type',
                ])
                ->latest('published_at')
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn (CommunityPost $post) => $this->formatPost($post, $user->id))
                ->values()
            : collect();

        $ownPosts = $canAccessCommunity
            ? CommunityPost::query()
                ->where('user_id', $user->id)
                ->with([
                    'user:id,pseudo,first_name',
                    'performedSession.workoutSession:id,name',
                    'performedSession.performances.exercise:id,name,sport_id,exercise_category_id',
                    'performedSession.performances.exercise.sport:id,name',
                    'performedSession.performances.exercise.category:id,name',
                    'performedSession.performances.metricValues.metric:id,key,label,unit,value_type',
                ])
                ->latest('published_at')
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn (CommunityPost $post) => $this->formatPost($post, $user->id))
                ->values()
            : collect();

        $following = $canAccessCommunity
            ? $user->following()
                ->orderBy('pseudo')
                ->get(['users.id', 'users.pseudo', 'users.first_name'])
                ->map(fn (User $followedUser) => $this->formatUserSummary($followedUser, true))
                ->values()
            : collect();

        $followers = $canAccessCommunity
            ? $user->followers()
                ->orderBy('pseudo')
                ->get(['users.id', 'users.pseudo', 'users.first_name'])
                ->map(fn (User $follower) => $this->formatUserSummary(
                    $follower,
                    $followingIds->contains($follower->id),
                ))
                ->values()
            : collect();

        return Inertia::render('Community', [
            'canAccessCommunity' => $canAccessCommunity,
            'activeSubscriptionPlan' => $activeSubscriptionPlan,
            'followingFeed' => $followingFeed,
            'ownPosts' => $ownPosts,
            'following' => $following,
            'followers' => $followers,
            'posts' => $followingFeed,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPremiumFeatures()) {
            return to_route('community')->withErrors([
                'community' => 'Le feed communautaire est reserve aux abonnements Premium.',
            ]);
        }

        $validatedData = $request->validate([
            'performed_session_id' => ['required', 'integer', 'exists:performed_sessions,id'],
            'title' => ['nullable', 'string', 'max:120'],
            'content' => ['nullable', 'string', 'max:1000'],
        ]);

        $performedSession = PerformedSession::query()
            ->with('communityPost')
            ->where('user_id', $user->id)
            ->find($validatedData['performed_session_id']);

        if (! $performedSession) {
            return back()->withErrors([
                'community' => 'Cette séance ne peut pas être partagée depuis votre compte.',
            ]);
        }

        if ($performedSession->completed_at === null) {
            return back()->withErrors([
                'community' => 'Vous pouvez partager uniquement une seance validee.',
            ]);
        }

        if ($performedSession->communityPost !== null) {
            return back()->withErrors([
                'community' => 'Cette seance est deja partagee.',
            ]);
        }

        CommunityPost::query()->create([
            'user_id' => $user->id,
            'performed_session_id' => $performedSession->id,
            'title' => $request->filled('title') ? $validatedData['title'] : null,
            'content' => $request->filled('content') ? $validatedData['content'] : null,
            'published_at' => now(),
        ]);

        return back()->with('success', 'Séance partagée dans le feed communautaire.');
    }

    public function update(Request $request, CommunityPost $communityPost): RedirectResponse
    {
        if ($communityPost->user_id !== $request->user()->id) {
            throw new AuthorizationException();
        }

        $validatedData = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'content' => ['nullable', 'string', 'max:1000'],
        ]);

        $communityPost->update([
            'title' => $request->filled('title') ? $validatedData['title'] : null,
            'content' => $request->filled('content') ? $validatedData['content'] : null,
        ]);

        return back()->with('success', 'Publication modifiée.');
    }

    public function destroy(Request $request, CommunityPost $communityPost): RedirectResponse
    {
        if ($communityPost->user_id !== $request->user()->id) {
            throw new AuthorizationException();
        }

        $communityPost->delete();

        return back()->with('success', 'Publication supprimée du feed communautaire.');
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasPremiumFeatures()) {
            abort(403);
        }

        $search = trim((string) $request->query('q', ''));

        if (mb_strlen($search) < 2) {
            return response()->json([
                'users' => [],
            ]);
        }

        $followingIds = $user->following()->pluck('users.id');

        $users = User::query()
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

        return response()->json([
            'users' => $users,
        ]);
    }

    public function showUser(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->hasPremiumFeatures()) {
            abort(403);
        }

        $user->loadCount(['followers', 'following', 'communityPosts']);

        $posts = CommunityPost::query()
            ->where('user_id', $user->id)
            ->with([
                'user:id,pseudo,first_name',
                'performedSession.workoutSession:id,name',
                'performedSession.performances.exercise:id,name,sport_id,exercise_category_id',
                'performedSession.performances.exercise.sport:id,name',
                'performedSession.performances.exercise.category:id,name',
                'performedSession.performances.metricValues.metric:id,key,label,unit,value_type',
            ])
            ->latest('published_at')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (CommunityPost $post) => $this->formatPost($post, $currentUser->id))
            ->values();

        return response()->json([
            'user' => [
                ...$this->formatUserSummary(
                    $user,
                    $currentUser->following()->whereKey($user->id)->exists(),
                ),
                'followers_count' => $user->followers_count,
                'following_count' => $user->following_count,
                'posts_count' => $user->community_posts_count,
                'posts' => $posts,
            ],
        ]);
    }

    public function followUser(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->hasPremiumFeatures()) {
            return to_route('community')->withErrors([
                'community' => 'Le suivi de membres est reserve aux abonnements Premium.',
            ]);
        }

        if ($currentUser->id === $user->id) {
            return back()->withErrors([
                'community' => 'Vous ne pouvez pas vous suivre vous-meme.',
            ]);
        }

        $currentUser->following()->syncWithoutDetaching([$user->id]);

        return back()->with('success', 'Membre suivi.');
    }

    public function unfollowUser(Request $request, User $user): RedirectResponse
    {
        $request->user()->following()->detach($user->id);

        return back()->with('success', 'Membre retire de vos abonnements.');
    }

    private function activeSubscriptionPlanName($user): ?string
    {
        return $user->subscriptions()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })
            ->with('subscriptionPlan:id,name')
            ->latest()
            ->first()
            ?->subscriptionPlan
            ?->name;
    }

    private function formatPost(CommunityPost $post, int $currentUserId): array
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

    private function formatUserSummary(User $user, bool $isFollowing): array
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
