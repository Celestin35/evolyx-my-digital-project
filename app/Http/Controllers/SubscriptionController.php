<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function store(Request $request, SubscriptionService $subscriptionService): RedirectResponse
    {
        $validatedData = $request->validate([
            'subscription_plan_name' => [
                'required',
                'string',
                Rule::exists('subscription_plans', 'name'),
            ],
        ]);

        $changed = $subscriptionService->changePlan(
            $request->user(),
            $validatedData['subscription_plan_name'],
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
