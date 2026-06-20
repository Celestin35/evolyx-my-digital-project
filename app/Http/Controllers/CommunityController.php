<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\User;
use App\Services\CommunityFeedService;
use App\Services\CommunityPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends Controller
{
    public function index(Request $request, CommunityFeedService $communityFeedService): Response
    {
        return Inertia::render('Community', $communityFeedService->pageDataFor($request->user()));
    }

    public function store(Request $request, CommunityPostService $communityPostService): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPremiumFeatures()) {
            return to_route('community')->withErrors([
                'community' => 'Le feed communautaire est réservé aux abonnements Premium.',
            ]);
        }

        $validatedData = $request->validate([
            'performed_session_id' => ['required', 'integer', 'exists:performed_sessions,id'],
            'title' => ['nullable', 'string', 'max:120'],
            'content' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($errors = $communityPostService->share(
            $user,
            $validatedData,
            $request->filled('title'),
            $request->filled('content'),
        )) {
            return back()->withErrors($errors);
        }

        return back()->with('success', 'Séance partagée dans le feed communautaire.');
    }

    public function update(
        Request $request,
        CommunityPost $communityPost,
        CommunityPostService $communityPostService,
    ): RedirectResponse {
        $validatedData = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'content' => ['nullable', 'string', 'max:1000'],
        ]);

        $communityPostService->update(
            $request->user(),
            $communityPost,
            $validatedData,
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
        $communityPostService->delete($request->user(), $communityPost);

        return back()->with('success', 'Publication supprimée du feed communautaire.');
    }

    public function searchUsers(Request $request, CommunityFeedService $communityFeedService): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasPremiumFeatures()) {
            abort(403);
        }

        return response()->json([
            'users' => $communityFeedService->searchUsers(
                $user,
                trim((string) $request->query('q', '')),
            ),
        ]);
    }

    public function showUser(Request $request, User $user, CommunityFeedService $communityFeedService): JsonResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->hasPremiumFeatures()) {
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
    ): RedirectResponse {
        $currentUser = $request->user();

        if (! $currentUser->hasPremiumFeatures()) {
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
