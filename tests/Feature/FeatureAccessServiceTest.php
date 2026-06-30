<?php

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\FeatureAccessService;

function makeFeatureAccessUser(string $planName, bool $adsEnabled, bool $premiumFeatures): User
{
    $user = User::factory()->create();
    $plan = SubscriptionPlan::query()->create([
        'name' => $planName,
        'price' => 0,
        'ads_enabled' => $adsEnabled,
        'premium_features' => $premiumFeatures,
    ]);

    Subscription::query()->create([
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'start_date' => now()->subDay(),
        'end_date' => null,
        'is_active' => true,
    ]);

    return $user;
}

test('feature access follows the free plus premium plan matrix', function () {
    $service = app(FeatureAccessService::class);

    $freeUser = makeFeatureAccessUser('Gratuit', adsEnabled: true, premiumFeatures: false);
    $plusUser = makeFeatureAccessUser('Plus', adsEnabled: false, premiumFeatures: false);
    $premiumUser = makeFeatureAccessUser('Premium', adsEnabled: false, premiumFeatures: true);

    expect($service->featureFlags($freeUser))->toMatchArray([
        'canAccessCommunity' => false,
        'canShareCommunityPost' => false,
        'canEditMacros' => false,
        'canViewPerformanceCharts' => false,
        'shouldDisplayAds' => true,
        'hasAdFreeExperience' => false,
    ])
        ->and($service->featureFlags($plusUser))->toMatchArray([
            'canAccessCommunity' => false,
            'canShareCommunityPost' => false,
            'canEditMacros' => false,
            'canViewPerformanceCharts' => false,
            'shouldDisplayAds' => false,
            'hasAdFreeExperience' => true,
        ])
        ->and($service->featureFlags($premiumUser))->toMatchArray([
            'canAccessCommunity' => true,
            'canShareCommunityPost' => true,
            'canEditMacros' => true,
            'canViewPerformanceCharts' => true,
            'shouldDisplayAds' => false,
            'hasAdFreeExperience' => true,
        ]);
});
