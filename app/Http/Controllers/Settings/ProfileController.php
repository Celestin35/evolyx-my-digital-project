<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request, SubscriptionService $subscriptionService): Response
    {
        $activeSubscription = $subscriptionService->activeSubscription($request->user());
        $subscriptionPlans = SubscriptionPlan::query()
            ->orderBy('price')
            ->get();

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'activeSubscription' => $activeSubscription ? [
                'plan_name' => $activeSubscription->subscriptionPlan?->name,
                'start_date' => $activeSubscription->start_date?->toDateString(),
                'end_date' => $activeSubscription->end_date?->toDateString(),
                'is_active' => $activeSubscription->is_active,
            ] : null,
            'subscriptionPlans' => $subscriptionPlans->map(fn (SubscriptionPlan $plan) => [
                'name' => $plan->name,
                'price' => $plan->price,
                'ads_enabled' => $plan->ads_enabled,
                'premium_features' => $plan->premium_features,
            ])->values(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
