<?php

namespace App\Services;

use Carbon\Carbon;

class CaloriesCalculationService
{
    public function calculateForUser(array $data): array
    {
        $bmr = $this->calculateBmr(
            weight: (float) $data['weight'],
            height: (int) $data['height'],
            age: (int) $data['age'],
            sex: (string) $data['sex'],
        );

        $maintenanceCalories = $this->calculateTdee(
            bmr: $bmr,
            activityLevel: (string) $data['activity_level'],
        );

        $targetCalories = $this->applyGoalAdjustment(
            maintenanceCalories: $maintenanceCalories,
            goal: $data['goal'] ?? null,
        );

        return [
            'bmr' => (int) round($bmr),
            'maintenance_calories' => (int) round($maintenanceCalories),
            'target_calories' => (int) round($targetCalories),
            'macros' => $this->calculateMacros((int) round($targetCalories)),
        ];
    }

    public function calculateGoalPlan(array $data): array
    {
        $bmr = $this->calculateBmr(
            weight: (float) $data['current_weight'],
            height: (int) $data['height'],
            age: (int) $data['age'],
            sex: (string) $data['sex'],
        );

        $maintenanceCalories = $this->calculateTdee(
            bmr: $bmr,
            activityLevel: (string) $data['activity_level'],
        );

        $weightDelta = (float) $data['target_weight'] - (float) $data['current_weight'];
        $weeklyWeightGoal = round((float) $data['weekly_weight_goal'], 2);
        $daysRemaining = $this->calculateGoalDurationInDays(
            weightDelta: $weightDelta,
            weeklyWeightGoal: $weeklyWeightGoal,
        );

        $dailyCalorieAdjustment = abs($weeklyWeightGoal) > 0
            ? (abs($weeklyWeightGoal) * 7700) / 7
            : 0.0;

        $rawTargetCalories = match (true) {
            $weeklyWeightGoal < 0 => $maintenanceCalories - $dailyCalorieAdjustment,
            $weeklyWeightGoal > 0 => $maintenanceCalories + $dailyCalorieAdjustment,
            default => $maintenanceCalories,
        };

        $safeTargetCalories = $this->applySafetyFloor(
            calories: $rawTargetCalories,
            sex: (string) $data['sex'],
        );

        $goalEndDate = $daysRemaining === null
            ? null
            : Carbon::today()->addDays($daysRemaining)->toDateString();

        return [
            'bmr' => (int) round($bmr),
            'maintenance_calories' => (int) round($maintenanceCalories),
            'target_calories' => (int) round($safeTargetCalories),
            'daily_calorie_adjustment' => (int) round($dailyCalorieAdjustment),
            'weekly_weight_change' => $weeklyWeightGoal,
            'days_remaining' => $daysRemaining,
            'goal_end_date' => $goalEndDate,
            'is_realistic' => abs($weeklyWeightGoal) <= 1,
            'macros' => $this->calculateMacros((int) round($safeTargetCalories)),
        ];
    }

    private function calculateGoalDurationInDays(float $weightDelta, float $weeklyWeightGoal): ?int
    {
        if ($weightDelta === 0.0 || $weeklyWeightGoal === 0.0) {
            return null;
        }

        $weeksRequired = abs($weightDelta) / abs($weeklyWeightGoal);

        return max(1, (int) ceil($weeksRequired * 7));
    }

    private function calculateBmr(float $weight, int $height, int $age, string $sex): float
    {
        $sex = strtolower($sex);

        if ($sex === 'female') {
            return (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
        }

        return (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
    }

    private function calculateTdee(float $bmr, string $activityLevel): float
    {
        $factors = [
            'sedentary' => 1.2,
            'light' => 1.375,
            'moderate' => 1.55,
            'active' => 1.725,
            'very_active' => 1.9,
        ];

        $factor = $factors[$activityLevel] ?? 1.55;

        return $bmr * $factor;
    }

    private function applyGoalAdjustment(float $maintenanceCalories, ?string $goal): float
    {
        return match ($goal) {
            'weight_loss' => $maintenanceCalories - 400,
            'muscle_gain' => $maintenanceCalories + 250,
            default => $maintenanceCalories,
        };
    }

    private function applySafetyFloor(float $calories, string $sex): float
    {
        $sex = strtolower($sex);
        $floor = $sex === 'female' ? 1200 : 1500;

        return max($calories, $floor);
    }

    private function calculateMacros(int $targetCalories): array
    {
        $proteinCalories = $targetCalories * 0.30;
        $fatCalories = $targetCalories * 0.25;
        $carbCalories = $targetCalories - $proteinCalories - $fatCalories;

        return [
            'protein' => (int) round($proteinCalories / 4),
            'fats' => (int) round($fatCalories / 9),
            'carbs' => (int) round($carbCalories / 4),
        ];
    }
}
