<?php

namespace App\Http\Controllers;

use App\Http\Requests\Community\StoreCommunityPostRequest;
use App\Http\Requests\Community\UpdateCommunityPostRequest;
use App\Models\CommunityPost;
use App\Models\PerformedSession;
use App\Models\User;
use App\Services\CommunityFeedService;
use App\Services\CommunityPostService;
use App\Services\FeatureAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends Controller
{
    public function index(Request $request, CommunityFeedService $communityFeedService): Response
    {
        return Inertia::render('Community', $communityFeedService->pageDataFor($request->user()));
    }

    public function store(
        StoreCommunityPostRequest $request,
        CommunityPostService $communityPostService,
        FeatureAccessService $featureAccessService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $featureAccessService->canShareCommunityPost($user)) {
            return to_route('community')->withErrors([
                'community' => 'Le feed communautaire est réservé aux abonnements Premium.',
            ]);
        }

        $performedSession = PerformedSession::query()
            ->with('communityPost')
            ->findOrFail($request->validated('performed_session_id'));

        if (Gate::denies('share', $performedSession)) {
            return back()->withErrors([
                'community' => 'Cette séance ne peut pas être partagée depuis votre compte.',
            ]);
        }

        if ($errors = $communityPostService->share(
            $user,
            $performedSession,
            $request->validated(),
            $request->filled('title'),
            $request->filled('content'),
        )) {
            return back()->withErrors($errors);
        }

        return back()->with('success', 'Séance partagée dans le feed communautaire.');
    }

    public function update(
        UpdateCommunityPostRequest $request,
        CommunityPost $communityPost,
        CommunityPostService $communityPostService,
    ): RedirectResponse {
        Gate::authorize('update', $communityPost);

        $communityPostService->update(
            $request->user(),
            $communityPost,
            $request->validated(),
            $request->filled('title'),
            $request->filled('content'),
        );

        return back()->with('success', 'Publication modifiée.');
    }

    public function destroy(
        Request $request,
        CommunityPost $communityPost,
        CommunityPostService $communityPostService,
    ): RedirectResponse {
        Gate::authorize('delete', $communityPost);

        $communityPostService->delete($request->user(), $communityPost);

        return back()->with('success', 'Publication supprimée du feed communautaire.');
    }

    public function searchUsers(
        Request $request,
        CommunityFeedService $communityFeedService,
        FeatureAccessService $featureAccessService,
    ): JsonResponse {
        $user = $request->user();

        if (! $featureAccessService->canAccessCommunity($user)) {
            abort(403);
        }

        return response()->json([
            'users' => $communityFeedService->searchUsers(
                $user,
                trim((string) $request->query('q', '')),
            ),
        ]);
    }

    public function showUser(
        Request $request,
        User $user,
        CommunityFeedService $communityFeedService,
        FeatureAccessService $featureAccessService,
    ): JsonResponse {
        $currentUser = $request->user();

        if (! $featureAccessService->canAccessCommunity($currentUser)) {
            abort(403);
        }

        return response()->json([
            'user' => $communityFeedService->profileFor($currentUser, $user),
        ]);
    }

    public function followUser(
        Request $request,
        User $user,
        CommunityPostService $communityPostService,
        FeatureAccessService $featureAccessService,
    ): RedirectResponse {
        $currentUser = $request->user();

        if (! $featureAccessService->canAccessCommunity($currentUser)) {
            return to_route('community')->withErrors([
                'community' => 'Le suivi de membres est réservé aux abonnements Premium.',
            ]);
        }

        if ($errors = $communityPostService->follow($currentUser, $user)) {
            return back()->withErrors($errors);
        }

        return back()->with('success', 'Membre suivi.');
    }

    public function unfollowUser(
        Request $request,
        User $user,
        CommunityPostService $communityPostService,
    ): RedirectResponse {
        $communityPostService->unfollow($request->user(), $user);

        return back()->with('success', 'Membre retiré de vos abonnements.');
    }
}
