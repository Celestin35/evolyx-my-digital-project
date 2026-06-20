<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class MacroService
{
    public function __construct(
        private readonly CaloriesCalculationService $caloriesCalculationService,
        private readonly FeatureAccessService $featureAccessService,
    ) {}

    public function nutritionDataFor(User $user): array
    {
        $user->load([
            'latestWeightEntry',
            'goals' => fn ($query) => $query
                ->where('is_active', true)
                ->with(['goalType', 'macronutrient'])
                ->latest()
                ->limit(1),
        ]);

        $activeGoal = $user->goals->first();
        $canEditMacros = $this->featureAccessService->canEditMacros($user);

        $goalPlan = $activeGoal
            ? $this->caloriesCalculationService->calculateGoalPlan([
                'current_weight' => (float) $user->current_weight,
                'target_weight' => (float) $activeGoal->target_weight,
                'weekly_weight_goal' => (float) $activeGoal->weekly_weight_goal,
                'height' => (int) $user->height,
                'age' => (int) $user->age,
                'sex' => (string) $user->sex,
                'activity_level' => (string) $user->activity_level,
            ])
            : null;

        return [
            'caloriesOverview' => [
                'current_weight' => $user->current_weight,
                'target_calories' => $activeGoal?->daily_calories,
                'maintenance_calories' => $goalPlan['maintenance_calories'] ?? null,
                'daily_adjustment' => $goalPlan['daily_calorie_adjustment'] ?? null,
                'goal_type' => $activeGoal?->goalType?->name,
                'goal_end_date' => $activeGoal?->goal_end_date?->toDateString(),
                'target_weight' => $activeGoal?->target_weight,
                'macros' => $canEditMacros && $activeGoal?->macronutrient ? [
                    'protein' => $activeGoal->macronutrient->protein,
                    'fats' => $activeGoal->macronutrient->fats,
                    'carbs' => $activeGoal->macronutrient->carbs,
                ] : null,
                'consumed_calories' => $activeGoal?->daily_calories
                    ? (int) round($activeGoal->daily_calories * 0.42)
                    : null,
            ],
            'can_edit_macros' => $canEditMacros,
            'active_subscription_plan' => $this->featureAccessService->activePlanName($user),
        ];
    }

    public function update(User $user, array $data): ?array
    {
        $user->load([
            'goals' => fn ($query) => $query
                ->where('is_active', true)
                ->with('macronutrient')
                ->latest()
                ->limit(1),
        ]);

        if (! $this->featureAccessService->canEditMacros($user)) {
            return ['macros' => 'Cette fonctionnalité est réservée à l’abonnement Premium.'];
        }

        $activeGoal = $user->goals->first();

        if (! $activeGoal || ! $activeGoal->macronutrient) {
            return ['macros' => 'Aucun objectif actif avec macros n’est disponible.'];
        }

        $targetCalories = ($data['protein'] * 4)
            + ($data['carbs'] * 4)
            + ($data['fats'] * 9);

        DB::transaction(function () use ($activeGoal, $data, $targetCalories) {
            $activeGoal->macronutrient()->update([
                'protein' => $data['protein'],
                'carbs' => $data['carbs'],
                'fats' => $data['fats'],
            ]);

            $activeGoal->update([
                'daily_calories' => $targetCalories,
            ]);
        });

        return null;
    }
}
