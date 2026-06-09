<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\CaloriesCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaloriesController extends Controller
{
    public function show(
        Request $request,
        CaloriesCalculationService $caloriesCalculationService,
    ): Response {
        $user = $request->user()->load([
            'latestWeightEntry',
            'goals' => fn ($query) => $query
                ->where('is_active', true)
                ->with(['goalType', 'macronutrient'])
                ->latest()
                ->limit(1),
            'subscriptions' => fn ($query) => $query
                ->where('is_active', true)
                ->with('subscriptionPlan')
                ->latest()
                ->limit(1),
        ]);

        $activeGoal = $user->goals->first();
        $activeSubscription = $user->subscriptions->first();
        $hasPremiumFeatures = $user->hasPremiumFeatures();

        $goalPlan = $activeGoal
            ? $caloriesCalculationService->calculateGoalPlan([
                'current_weight' => (float) $user->current_weight,
                'target_weight' => (float) $activeGoal->target_weight,
                'weekly_weight_goal' => (float) $activeGoal->weekly_weight_goal,
                'height' => (int) $user->height,
                'age' => (int) $user->age,
                'sex' => (string) $user->sex,
                'activity_level' => (string) $user->activity_level,
            ])
            : null;

        return Inertia::render('Nutrition', [
            'caloriesOverview' => [
                'current_weight' => $user->current_weight,
                'target_calories' => $activeGoal?->daily_calories,
                'maintenance_calories' => $goalPlan['maintenance_calories'] ?? null,
                'daily_adjustment' => $goalPlan['daily_calorie_adjustment'] ?? null,
                'goal_type' => $activeGoal?->goalType?->name,
                'goal_end_date' => $activeGoal?->goal_end_date?->toDateString(),
                'target_weight' => $activeGoal?->target_weight,
                'macros' => $hasPremiumFeatures && $activeGoal?->macronutrient ? [
                    'protein' => $activeGoal->macronutrient->protein,
                    'fats' => $activeGoal->macronutrient->fats,
                    'carbs' => $activeGoal->macronutrient->carbs,
                ] : null,
                // Tracking arrives later; for now we keep a static, visible example state.
                'consumed_calories' => $activeGoal?->daily_calories
                    ? (int) round($activeGoal->daily_calories * 0.42)
                    : null,
            ],
            'can_edit_macros' => $hasPremiumFeatures,
            'active_subscription_plan' => $activeSubscription?->subscriptionPlan?->name,
        ]);
    }

    public function updateMacros(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'protein' => ['required', 'integer', 'min:0', 'max:600'],
            'carbs' => ['required', 'integer', 'min:0', 'max:900'],
            'fats' => ['required', 'integer', 'min:0', 'max:300'],
        ]);

        $user = $request->user()->load([
            'subscriptions' => fn ($query) => $query
                ->where('is_active', true)
                ->with('subscriptionPlan')
                ->latest()
                ->limit(1),
            'goals' => fn ($query) => $query
                ->where('is_active', true)
                ->with('macronutrient')
                ->latest()
                ->limit(1),
        ]);

        if (! $user->hasPremiumFeatures()) {
            return to_route('nutrition')->withErrors([
                'macros' => 'Cette fonctionnalité est réservée à l’abonnement Premium.',
            ]);
        }

        $activeGoal = $user->goals->first();

        if (! $activeGoal || ! $activeGoal->macronutrient) {
            return to_route('nutrition')->withErrors([
                'macros' => 'Aucun objectif actif avec macros n’est disponible.',
            ]);
        }

        $targetCalories = ($validatedData['protein'] * 4)
            + ($validatedData['carbs'] * 4)
            + ($validatedData['fats'] * 9);

        DB::transaction(function () use ($activeGoal, $validatedData, $targetCalories) {
            $activeGoal->macronutrient()->update([
                'protein' => $validatedData['protein'],
                'carbs' => $validatedData['carbs'],
                'fats' => $validatedData['fats'],
            ]);

            $activeGoal->update([
                'daily_calories' => $targetCalories,
            ]);
        });

        return to_route('nutrition')->with(
            'success',
            'Macros mises à jour avec succès.',
        );
    }

    public function getUserData(Request $request, CaloriesCalculationService $caloriesCalculationService)
    {
        $user = $request->user()->load('latestWeightEntry');

        if (! $user->current_weight) {
            return response()->json([
                'message' => 'Aucune entrée de poids disponible pour cet utilisateur.',
            ], 422);
        }

        return response()->json([
            'user_data' => [
                'age' => $user->age,
                'weight' => $user->current_weight,
                'height' => $user->height,
                'sex' => $user->sex,
                'activity_level' => $user->activity_level,
            ],
            'calculation' => $caloriesCalculationService->calculateForUser([
                'age' => $user->age,
                'weight' => $user->current_weight,
                'height' => $user->height,
                'sex' => $user->sex,
                'activity_level' => $user->activity_level,
            ]),
        ]);
    }
}
