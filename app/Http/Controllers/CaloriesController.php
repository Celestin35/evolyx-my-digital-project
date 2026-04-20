<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Services\CaloriesCalculationService;
use Illuminate\Http\Request;

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
        ]);

        $activeGoal = $user->goals->first();

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
                'macros' => $activeGoal?->macronutrient ? [
                    'protein' => $activeGoal->macronutrient->protein,
                    'fats' => $activeGoal->macronutrient->fats,
                    'carbs' => $activeGoal->macronutrient->carbs,
                ] : null,
                // Tracking arrives later; for now we keep a static, visible example state.
                'consumed_calories' => $activeGoal?->daily_calories
                    ? (int) round($activeGoal->daily_calories * 0.42)
                    : null,
            ],
        ]);
    }

    public function getUserData(Request $request, CaloriesCalculationService $caloriesCalculationService)
    {
        $user = $request->user()->load('latestWeightEntry');

        if (! $user->current_weight) {
            return response()->json([
                'message' => 'Aucune entree de poids disponible pour cet utilisateur.',
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
