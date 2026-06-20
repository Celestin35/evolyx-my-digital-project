<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function activeSubscription(User $user, array $with = ['subscriptionPlan']): ?Subscription
    {
        return $user->subscriptions()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })
            ->with($with)
            ->latest('start_date')
            ->first();
    }

    public function activePlanName(User $user): ?string
    {
        return $this->activeSubscription($user, ['subscriptionPlan:id,name'])
            ?->subscriptionPlan
            ?->name;
    }

    public function canViewPerformanceCharts(User $user): bool
    {
        $activeSubscription = $this->activeSubscription($user, ['subscriptionPlan:id,name,premium_features']);

        return $activeSubscription?->subscriptionPlan?->name === 'Plus'
            || (bool) $activeSubscription?->subscriptionPlan?->premium_features;
    }

    public function changePlan(User $user, string $planName): bool
    {
        $currentSubscription = $user->load([
            'subscriptions' => fn ($query) => $query
                ->where('is_active', true)
                ->with('subscriptionPlan')
                ->latest()
                ->limit(1),
        ])->subscriptions->first();

        $selectedPlan = SubscriptionPlan::query()
            ->where('name', $planName)
            ->firstOrFail();

        if ($currentSubscription?->subscriptionPlan?->name === $selectedPlan->name) {
            return false;
        }

        DB::transaction(function () use ($user, $selectedPlan) {
            $user->subscriptions()
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'end_date' => now(),
                ]);

            $user->subscriptions()->create([
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'is_active' => true,
                'subscription_plan_id' => $selectedPlan->id,
            ]);
        });

        return true;
    }
}
