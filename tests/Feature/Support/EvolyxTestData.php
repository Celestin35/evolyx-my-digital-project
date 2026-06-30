<?php

use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\Goal;
use App\Models\GoalType;
use App\Models\Macronutrient;
use App\Models\Metric;
use App\Models\PerformedSession;
use App\Models\Sport;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WeightEntry;
use App\Models\WorkoutSession;

function evolyxSport(string $name = 'Fitness / musculation'): Sport
{
    return Sport::query()->firstOrCreate(
        ['name' => $name],
        ['display_name' => 'Musculation', 'sort_order' => 1],
    );
}

function evolyxExerciseCategory(string $name = 'Force'): ExerciseCategory
{
    return ExerciseCategory::query()->where('name', $name)->first()
        ?? ExerciseCategory::query()->forceCreate(['name' => $name]);
}

function evolyxMetric(
    string $key,
    string $label,
    ?string $unit = null,
    string $valueType = 'decimal',
    int $sortOrder = 0,
): Metric {
    return Metric::query()->firstOrCreate(
        ['key' => $key],
        [
            'label' => $label,
            'unit' => $unit,
            'value_type' => $valueType,
            'sort_order' => $sortOrder,
        ],
    );
}

function evolyxUserWithSport(?Sport $sport = null): User
{
    $user = User::factory()->create();
    $user->sports()->syncWithoutDetaching([($sport ?? evolyxSport())->id]);

    return $user;
}

function evolyxExercise(User $user, ?Sport $sport = null, array $metrics = []): Exercise
{
    $sport ??= evolyxSport();

    $exercise = Exercise::query()->create([
        'name' => 'Développé couché',
        'description' => null,
        'sport_id' => $sport->id,
        'exercise_category_id' => evolyxExerciseCategory()->id,
        'user_id' => $user->id,
    ]);

    if ($metrics !== []) {
        $exercise->metrics()->sync(
            collect($metrics)
                ->values()
                ->mapWithKeys(fn (Metric $metric, int $index) => [
                    $metric->id => ['sort_order' => $index + 1],
                ])
                ->all(),
        );
    }

    return $exercise;
}

function evolyxWorkoutSession(User $user, ?Exercise $exercise = null): WorkoutSession
{
    $exercise ??= evolyxExercise($user);

    $workoutSession = WorkoutSession::query()->create([
        'name' => 'Séance haut du corps',
        'description' => 'Push',
        'user_id' => $user->id,
    ]);

    $workoutSession->exercises()->attach($exercise->id, ['position' => 1]);

    return $workoutSession;
}

function evolyxPerformedSession(User $user, ?WorkoutSession $workoutSession = null, bool $completed = false): PerformedSession
{
    return PerformedSession::query()->create([
        'user_id' => $user->id,
        'workout_session_id' => ($workoutSession ?? evolyxWorkoutSession($user))->id,
        'performed_at' => now()->subDay(),
        'completed_at' => $completed ? now() : null,
        'notes' => null,
    ]);
}

function evolyxSubscriptionPlan(string $name, bool $adsEnabled, bool $premiumFeatures): SubscriptionPlan
{
    return SubscriptionPlan::query()->firstOrCreate(
        ['name' => $name],
        [
            'price' => $name === 'Gratuit' ? 0 : 9.99,
            'ads_enabled' => $adsEnabled,
            'premium_features' => $premiumFeatures,
        ],
    );
}

function evolyxSubscribe(User $user, string $planName): Subscription
{
    $plan = match ($planName) {
        'Premium' => evolyxSubscriptionPlan('Premium', adsEnabled: false, premiumFeatures: true),
        'Plus' => evolyxSubscriptionPlan('Plus', adsEnabled: false, premiumFeatures: false),
        default => evolyxSubscriptionPlan('Gratuit', adsEnabled: true, premiumFeatures: false),
    };

    return Subscription::query()->create([
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'start_date' => now()->subDay(),
        'end_date' => null,
        'is_active' => true,
    ]);
}

function evolyxGoalTypes(): void
{
    foreach (['Perte de poids', 'Prise de masse', 'Maintien'] as $name) {
        GoalType::query()->firstOrCreate(['name' => $name]);
    }
}

function evolyxWeightEntry(User $user, float $weight = 80.0): WeightEntry
{
    return WeightEntry::query()->create([
        'user_id' => $user->id,
        'weight' => $weight,
        'body_fat' => null,
    ]);
}

function evolyxActiveGoal(User $user): Goal
{
    evolyxGoalTypes();

    $macros = Macronutrient::query()->create([
        'protein' => 150,
        'carbs' => 250,
        'fats' => 70,
    ]);

    return Goal::query()->create([
        'user_id' => $user->id,
        'target_weight' => 75,
        'weekly_weight_goal' => -0.5,
        'daily_calories' => 2230,
        'is_active' => true,
        'goal_end_date' => now()->addWeeks(10)->toDateString(),
        'goal_type_id' => GoalType::query()->where('name', 'Perte de poids')->value('id'),
        'macronutrient_id' => $macros->id,
    ]);
}
