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

        $daysRemaining = max(1, Carbon::parse($data['goal_end_date'])->diffInDays(now()));
        $weightDelta = (float) $data['target_weight'] - (float) $data['current_weight'];
        $dailyCalorieAdjustment = (abs($weightDelta) * 7700) / $daysRemaining;

        $rawTargetCalories = match (true) {
            $weightDelta < 0 => $maintenanceCalories - $dailyCalorieAdjustment,
            $weightDelta > 0 => $maintenanceCalories + $dailyCalorieAdjustment,
            default => $maintenanceCalories,
        };

        $safeTargetCalories = $this->applySafetyFloor(
            calories: $rawTargetCalories,
            sex: (string) $data['sex'],
        );

        $weeklyWeightChange = $daysRemaining > 0
            ? ($weightDelta / $daysRemaining) * 7
            : 0.0;

        return [
            'bmr' => (int) round($bmr),
            'maintenance_calories' => (int) round($maintenanceCalories),
            'target_calories' => (int) round($safeTargetCalories),
            'daily_calorie_adjustment' => (int) round($dailyCalorieAdjustment),
            'weekly_weight_change' => round($weeklyWeightChange, 2),
            'days_remaining' => $daysRemaining,
            'is_realistic' => abs($weeklyWeightChange) <= 1,
            'macros' => $this->calculateMacros((int) round($safeTargetCalories)),
        ];
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
