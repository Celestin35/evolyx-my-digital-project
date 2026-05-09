<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'subscription_plan_name' => [
                'required',
                'string',
                Rule::exists('subscription_plans', 'name'),
            ],
        ]);

        $user = $request->user()->load([
            'subscriptions' => fn ($query) => $query
                ->where('is_active', true)
                ->with('subscriptionPlan')
                ->latest()
                ->limit(1),
        ]);

        $currentSubscription = $user->subscriptions->first();
        $selectedPlan = SubscriptionPlan::query()
            ->where('name', $validatedData['subscription_plan_name'])
            ->firstOrFail();

        if ($currentSubscription?->subscriptionPlan?->name === $selectedPlan->name) {
            return to_route('profile.edit')->with(
                'success',
                'Cet abonnement est deja actif.',
            );
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

        return to_route('profile.edit')->with(
            'success',
            'Abonnement mis à jour avec succès.',
        );
    }
}
