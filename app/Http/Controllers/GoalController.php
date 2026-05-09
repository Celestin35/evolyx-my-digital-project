<?php

namespace App\Http\Controllers;

use App\Models\GoalType;
use App\Models\Macronutrient;
use App\Services\CaloriesCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoalController extends Controller
{
    public function store(Request $request, CaloriesCalculationService $caloriesCalculationService)
    {
        $user = $request->user()->load('latestWeightEntry');

        if (! $user->current_weight) {
            return response()->json([
                'message' => 'Aucune entree de poids disponible pour cet utilisateur.',
            ], 422);
        }

        $validatedData = $request->validate([
            'target_weight' => ['required', 'numeric', 'min:20', 'max:600'],
            'weekly_weight_goal' => [
                'required',
                'numeric',
                'min:-1',
                'max:1',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $allowedValues = [-1.0, -0.75, -0.5, -0.25, 0.0, 0.25, 0.5, 0.75, 1.0];

                    if (! in_array((float) $value, $allowedValues, true)) {
                        $fail('Le rythme hebdomadaire doit correspondre a une option disponible.');
                    }
                },
            ],
        ]);

        $this->ensureGoalDirectionIsValid(
            currentWeight: (float) $user->current_weight,
            targetWeight: (float) $validatedData['target_weight'],
            weeklyWeightGoal: (float) $validatedData['weekly_weight_goal'],
        );

        $goalTypeKey = $this->defineGoalType(
            currentWeight: (float) $user->current_weight,
            targetWeight: (float) $validatedData['target_weight'],
        );

        $goalType = GoalType::query()
            ->where('name', $this->mapGoalTypeToDatabaseName($goalTypeKey))
            ->first();

        if (! $goalType) {
            return response()->json([
                'message' => 'Le type d objectif correspondant est introuvable.',
            ], 422);
        }

        $goalPlan = $caloriesCalculationService->calculateGoalPlan([
            'current_weight' => (float) $user->current_weight,
            'target_weight' => (float) $validatedData['target_weight'],
            'weekly_weight_goal' => (float) $validatedData['weekly_weight_goal'],
            'height' => (int) $user->height,
            'age' => (int) $user->age,
            'sex' => (string) $user->sex,
            'activity_level' => (string) $user->activity_level,
        ]);

        DB::transaction(function () use ($user, $validatedData, $goalType, $goalPlan) {
            $user->goals()->where('is_active', true)->update(['is_active' => false]);

            $macronutrient = Macronutrient::query()->create([
                'fats' => $goalPlan['macros']['fats'],
                'carbs' => $goalPlan['macros']['carbs'],
                'protein' => $goalPlan['macros']['protein'],
            ]);

            $user->goals()->create([
                'target_weight' => $validatedData['target_weight'],
                'weekly_weight_goal' => $validatedData['weekly_weight_goal'],
                'daily_calories' => $goalPlan['target_calories'],
                'is_active' => true,
                'goal_end_date' => $goalPlan['goal_end_date'],
                'goal_type_id' => $goalType->id,
                'macronutrient_id' => $macronutrient->id,
            ]);
        });

        return to_route('profile')->with('success', 'Objectif enregistré avec succès.');
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
                'weekly_weight_goal' => 'Le rythme hebdomadaire doit etre a 0 pour un maintien.',
            ]);
        }

        if ($targetWeight > $currentWeight && $weeklyWeightGoal <= 0.0) {
            throw ValidationException::withMessages([
                'weekly_weight_goal' => 'Le rythme hebdomadaire doit etre positif pour une prise de poids.',
            ]);
        }

        if ($targetWeight < $currentWeight && $weeklyWeightGoal >= 0.0) {
            throw ValidationException::withMessages([
                'weekly_weight_goal' => 'Le rythme hebdomadaire doit etre negatif pour une perte de poids.',
            ]);
        }
    }
}
