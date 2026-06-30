<?php

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\FeatureAccessService;
use App\Services\SubscriptionService;
use Mockery\MockInterface;

afterEach(function () {
    Mockery::close();
});

test('it exposes the expected feature flags for each subscription plan', function (
    string $planName,
    bool $adsEnabled,
    bool $premiumFeatures,
    array $expectedFlags,
) {
    // Arrange
    $user = new User;
    $plan = new SubscriptionPlan([
        'name' => $planName,
        'ads_enabled' => $adsEnabled,
        'premium_features' => $premiumFeatures,
    ]);
    $subscription = new Subscription;
    $subscription->setRelation('subscriptionPlan', $plan);

    $subscriptionService = Mockery::mock(SubscriptionService::class, function (MockInterface $mock) use ($user, $subscription) {
        $mock->shouldReceive('activeSubscription')
            ->times(6)
            ->with(
                $user,
                ['subscriptionPlan:id,name,ads_enabled,premium_features'],
            )
            ->andReturn($subscription);
    });
    $service = new FeatureAccessService($subscriptionService);

    // Act
    $flags = $service->featureFlags($user);

    // Assert
    expect($flags)->toBe($expectedFlags);
})->with([
    'free' => [
        FeatureAccessService::PLAN_FREE,
        true,
        false,
        [
            'canAccessCommunity' => false,
            'canShareCommunityPost' => false,
            'canEditMacros' => false,
            'canViewPerformanceCharts' => false,
            'shouldDisplayAds' => true,
            'hasAdFreeExperience' => false,
        ],
    ],
    'plus' => [
        FeatureAccessService::PLAN_PLUS,
        false,
        false,
        [
            'canAccessCommunity' => false,
            'canShareCommunityPost' => false,
            'canEditMacros' => false,
            'canViewPerformanceCharts' => false,
            'shouldDisplayAds' => false,
            'hasAdFreeExperience' => true,
        ],
    ],
    'premium' => [
        FeatureAccessService::PLAN_PREMIUM,
        false,
        true,
        [
            'canAccessCommunity' => true,
            'canShareCommunityPost' => true,
            'canEditMacros' => true,
            'canViewPerformanceCharts' => true,
            'shouldDisplayAds' => false,
            'hasAdFreeExperience' => true,
        ],
    ],
]);
