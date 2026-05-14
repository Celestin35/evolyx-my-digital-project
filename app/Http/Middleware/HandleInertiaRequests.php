<?php

namespace App\Http\Middleware;

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
                $activeSubscription = $request->user()
                    ?->subscriptions()
                    ->where('is_active', true)
                    ->where(function ($query) {
                        $query->whereNull('end_date')
                            ->orWhere('end_date', '>', now());
                    })
                    ->with('subscriptionPlan:id,name,ads_enabled')
                    ->latest('start_date')
                    ->first();

                return [
                    'enabled' => (bool) $activeSubscription?->subscriptionPlan?->ads_enabled,
                    'popup_interval_minutes' => 5,
                    'close_delay_seconds' => 3,
                ];
            },
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
