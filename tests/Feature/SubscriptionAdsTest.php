<?php

use App\Models\User;
use App\Services\FeatureAccessService;

test('free plus and premium expose the expected ads and premium feature flags', function (string $planName, bool $shouldDisplayAds, bool $hasPremiumFeatures) {
    $user = User::factory()->create();
    evolyxSubscribe($user, $planName);

    $features = app(FeatureAccessService::class)->featureFlags($user);

    expect($features['shouldDisplayAds'])->toBe($shouldDisplayAds)
        ->and($features['hasAdFreeExperience'])->toBe(! $shouldDisplayAds)
        ->and($features['canAccessCommunity'])->toBe($hasPremiumFeatures)
        ->and($features['canShareCommunityPost'])->toBe($hasPremiumFeatures)
        ->and($features['canEditMacros'])->toBe($hasPremiumFeatures)
        ->and($features['canViewPerformanceCharts'])->toBe($hasPremiumFeatures);
})->with([
    'free displays ads' => ['Gratuit', true, false],
    'plus hides ads only' => ['Plus', false, false],
    'premium hides ads and unlocks features' => ['Premium', false, true],
]);

test('inertia shared ads reflect the active subscription plan', function (string $planName, bool $enabled) {
    $user = User::factory()->create();
    evolyxSubscribe($user, $planName);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('ads.enabled', $enabled)
        ->where('features.shouldDisplayAds', $enabled),
    );
})->with([
    'free' => ['Gratuit', true],
    'plus' => ['Plus', false],
    'premium' => ['Premium', false],
]);
