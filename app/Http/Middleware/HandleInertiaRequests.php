<?php

namespace App\Http\Middleware;

use App\Services\FeatureAccessService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
            'auth' => [
                'user' => $request->user(),
            ],
            'ads' => function () use ($request) {
                $user = $request->user();

                return [
                    'enabled' => $user
                        ? app(FeatureAccessService::class)->shouldDisplayAds($user)
                        : false,
                    'has_ad_free_experience' => $user
                        ? app(FeatureAccessService::class)->hasAdFreeExperience($user)
                        : true,
                    'popup_interval_minutes' => 5,
                    'close_delay_seconds' => 3,
                ];
            },
            'features' => fn () => $request->user()
                ? app(FeatureAccessService::class)->featureFlags($request->user())
                : null,
            'subscription' => fn () => [
                'active_plan_name' => $request->user()
                    ? app(FeatureAccessService::class)->activePlanName($request->user())
                    : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
