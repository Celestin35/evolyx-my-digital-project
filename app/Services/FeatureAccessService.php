<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;

class FeatureAccessService
{
    public const PLAN_FREE = 'Gratuit';

    public const PLAN_PLUS = 'Plus';

    public const PLAN_PREMIUM = 'Premium';

    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function activeSubscription(User $user): ?Subscription
    {
        return $this->subscriptionService->activeSubscription(
            $user,
            ['subscriptionPlan:id,name,ads_enabled,premium_features'],
        );
    }

    public function activePlanName(User $user): ?string
    {
        return $this->activeSubscription($user)?->subscriptionPlan?->name;
    }

    public function canAccessCommunity(User $user): bool
    {
        return $this->hasPremiumFeatures($user);
    }

    public function canShareCommunityPost(User $user): bool
    {
        return $this->canAccessCommunity($user);
    }

    public function canEditMacros(User $user): bool
    {
        return $this->hasPremiumFeatures($user);
    }

    public function canViewPerformanceCharts(User $user): bool
    {
        return $this->hasPremiumFeatures($user);
    }

    public function shouldDisplayAds(User $user): bool
    {
        return (bool) $this->activeSubscription($user)?->subscriptionPlan?->ads_enabled;
    }

    public function hasAdFreeExperience(User $user): bool
    {
        return ! $this->shouldDisplayAds($user);
    }

    public function featureFlags(User $user): array
    {
        return [
            'canAccessCommunity' => $this->canAccessCommunity($user),
            'canShareCommunityPost' => $this->canShareCommunityPost($user),
            'canEditMacros' => $this->canEditMacros($user),
            'canViewPerformanceCharts' => $this->canViewPerformanceCharts($user),
            'shouldDisplayAds' => $this->shouldDisplayAds($user),
            'hasAdFreeExperience' => $this->hasAdFreeExperience($user),
        ];
    }

    public function hasPremiumFeatures(User $user): bool
    {
        return (bool) $this->activeSubscription($user)?->subscriptionPlan?->premium_features;
    }
}
