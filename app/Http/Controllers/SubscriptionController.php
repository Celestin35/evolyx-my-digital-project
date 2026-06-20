<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subscriptions\StoreSubscriptionRequest;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;

class SubscriptionController extends Controller
{
    public function store(StoreSubscriptionRequest $request, SubscriptionService $subscriptionService): RedirectResponse
    {
        $changed = $subscriptionService->changePlan(
            $request->user(),
            $request->validated('subscription_plan_name'),
        );

        if (! $changed) {
            return to_route('profile.edit')->with(
                'success',
                'Cet abonnement est déjà actif.',
            );
        }

        return to_route('profile.edit')->with(
            'success',
            'Abonnement mis à jour avec succès.',
        );
    }
}
