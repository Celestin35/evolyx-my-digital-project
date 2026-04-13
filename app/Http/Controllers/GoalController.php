<?php

namespace App\Http\Controllers;

use App\Models\GoalType;
use App\Models\Macronutrient;
use App\Services\CaloriesCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'target_weight' => ['required', 'numeric', 'min:20', 'max:500'],
            'goal_end_date' => ['required', 'date', 'after:today'],
        ]);

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
            'goal_end_date' => $validatedData['goal_end_date'],
            'height' => (int) $user->height,
            'age' => (int) $user->age,
            'sex' => (string) $user->sex,
            'activity_level' => (string) $user->activity_level,
        ]);

        $goal = DB::transaction(function () use ($user, $validatedData, $goalType, $goalPlan) {
            $user->goals()->where('is_active', true)->update(['is_active' => false]);

            $macronutrient = Macronutrient::query()->create([
                'fats' => $goalPlan['macros']['fats'],
                'carbs' => $goalPlan['macros']['carbs'],
                'protein' => $goalPlan['macros']['protein'],
            ]);

            return $user->goals()->create([
                'target_weight' => $validatedData['target_weight'],
                'daily_calories' => $goalPlan['target_calories'],
                'is_active' => true,
                'goal_end_date' => $validatedData['goal_end_date'],
                'goal_type_id' => $goalType->id,
                'macronutrient_id' => $macronutrient->id,
            ]);
        });

        return response()->json([
            'message' => 'Goal created successfully.',
            'goal' => $goal,
            'goal_plan' => $goalPlan,
        ], 201);
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
}
