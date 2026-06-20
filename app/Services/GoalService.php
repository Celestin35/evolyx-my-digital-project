<?php

namespace App\Services;

use App\Models\GoalType;
use App\Models\Macronutrient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoalService
{
    public function __construct(
        private readonly CaloriesCalculationService $caloriesCalculationService,
    ) {}

    public function create(User $user, array $data): ?array
    {
        $user->load('latestWeightEntry');

        if (! $user->current_weight) {
            return ['message' => 'Aucune entrée de poids disponible pour cet utilisateur.'];
        }

        $this->ensureGoalDirectionIsValid(
            currentWeight: (float) $user->current_weight,
            targetWeight: (float) $data['target_weight'],
            weeklyWeightGoal: (float) $data['weekly_weight_goal'],
        );

        $goalType = GoalType::query()
            ->where('name', $this->mapGoalTypeToDatabaseName($this->defineGoalType(
                currentWeight: (float) $user->current_weight,
                targetWeight: (float) $data['target_weight'],
            )))
            ->first();

        if (! $goalType) {
            return ['message' => 'Le type d’objectif correspondant est introuvable.'];
        }

        $goalPlan = $this->caloriesCalculationService->calculateGoalPlan([
            'current_weight' => (float) $user->current_weight,
            'target_weight' => (float) $data['target_weight'],
            'weekly_weight_goal' => (float) $data['weekly_weight_goal'],
            'height' => (int) $user->height,
            'age' => (int) $user->age,
            'sex' => (string) $user->sex,
            'activity_level' => (string) $user->activity_level,
        ]);

        DB::transaction(function () use ($user, $data, $goalType, $goalPlan) {
            $user->goals()->where('is_active', true)->update(['is_active' => false]);

            $macronutrient = Macronutrient::query()->create([
                'fats' => $goalPlan['macros']['fats'],
                'carbs' => $goalPlan['macros']['carbs'],
                'protein' => $goalPlan['macros']['protein'],
            ]);

            $user->goals()->create([
                'target_weight' => $data['target_weight'],
                'weekly_weight_goal' => $data['weekly_weight_goal'],
                'daily_calories' => $goalPlan['target_calories'],
                'is_active' => true,
                'goal_end_date' => $goalPlan['goal_end_date'],
                'goal_type_id' => $goalType->id,
                'macronutrient_id' => $macronutrient->id,
            ]);
        });

        return null;
    }

    private function defineGoalType(float $currentWeight, float $targetWeight): string
    {
        if ($targetWeight < $currentWeight) {
            return 'weight_loss';
        }

        if ($targetWeight > $currentWeight) {
            return 'muscle_gain';
        }

        return 'maintenance';
    }

    private function mapGoalTypeToDatabaseName(string $goalType): string
    {
        return match ($goalType) {
            'weight_loss' => 'Perte de poids',
            'muscle_gain' => 'Prise de masse',
            default => 'Maintien',
        };
    }

    private function ensureGoalDirectionIsValid(
        float $currentWeight,
        float $targetWeight,
        float $weeklyWeightGoal,
    ): void {
        if ($targetWeight === $currentWeight && $weeklyWeightGoal !== 0.0) {
            throw ValidationException::withMessages([
                'weekly_weight_goal' => 'Le rythme hebdomadaire doit être à 0 pour un maintien.',
            ]);
        }

        if ($targetWeight > $currentWeight && $weeklyWeightGoal <= 0.0) {
            throw ValidationException::withMessages([
                'weekly_weight_goal' => 'Le rythme hebdomadaire doit être positif pour une prise de poids.',
            ]);
        }

        if ($targetWeight < $currentWeight && $weeklyWeightGoal >= 0.0) {
            throw ValidationException::withMessages([
                'weekly_weight_goal' => 'Le rythme hebdomadaire doit être négatif pour une perte de poids.',
            ]);
        }
    }
}
