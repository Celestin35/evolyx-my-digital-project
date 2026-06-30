<?php

use App\Models\Goal;
use App\Models\Macronutrient;
use App\Models\User;
use App\Services\CaloriesCalculationService;

test('calorie calculation returns maintenance target and macros', function () {
    $calculation = app(CaloriesCalculationService::class)->calculateForUser([
        'weight' => 80,
        'height' => 180,
        'age' => 30,
        'sex' => 'male',
        'activity_level' => 'moderate',
    ]);

    expect($calculation['bmr'])->toBe(1780)
        ->and($calculation['maintenance_calories'])->toBe(2759)
        ->and($calculation['target_calories'])->toBe(2759)
        ->and($calculation['macros'])->toHaveKeys(['protein', 'fats', 'carbs']);
});

test('a user can create weight loss gain and maintenance goals', function (float $targetWeight, float $weeklyGoal, string $goalTypeName) {
    evolyxGoalTypes();
    $user = User::factory()->create([
        'sex' => 'male',
        'height' => 180,
        'activity_level' => 'moderate',
        'birth_date' => now()->subYears(30)->toDateString(),
    ]);
    evolyxWeightEntry($user, 80);

    $response = $this
        ->actingAs($user)
        ->post(route('goals.store'), [
            'target_weight' => $targetWeight,
            'weekly_weight_goal' => $weeklyGoal,
        ]);

    $response->assertRedirect(route('profile'));

    $goal = Goal::query()->where('user_id', $user->id)->latest()->firstOrFail();
    expect($goal->goalType->name)->toBe($goalTypeName)
        ->and($goal->daily_calories)->toBeGreaterThan(0)
        ->and($goal->macronutrient)->not->toBeNull();
})->with([
    'weight loss' => [75.0, -0.5, 'Perte de poids'],
    'weight gain' => [85.0, 0.5, 'Prise de masse'],
    'maintenance' => [80.0, 0.0, 'Maintien'],
]);

test('premium users can update macros and target calories', function () {
    $user = User::factory()->create();
    evolyxSubscribe($user, 'Premium');
    $goal = evolyxActiveGoal($user);

    $response = $this
        ->actingAs($user)
        ->patch(route('nutrition.macros.update'), [
            'protein' => 180,
            'carbs' => 220,
            'fats' => 60,
        ]);

    $response->assertRedirect(route('nutrition'));
    $goal->refresh();

    expect($goal->daily_calories)->toBe((180 * 4) + (220 * 4) + (60 * 9));
    $this->assertDatabaseHas('macronutrients', [
        'id' => $goal->macronutrient_id,
        'protein' => 180,
        'carbs' => 220,
        'fats' => 60,
    ]);
});

test('free and plus users cannot update macros', function (string $planName) {
    $user = User::factory()->create();
    evolyxSubscribe($user, $planName);
    $goal = evolyxActiveGoal($user);
    $originalMacros = Macronutrient::query()->findOrFail($goal->macronutrient_id)->only(['protein', 'carbs', 'fats']);

    $response = $this
        ->actingAs($user)
        ->patch(route('nutrition.macros.update'), [
            'protein' => 180,
            'carbs' => 220,
            'fats' => 60,
        ]);

    $response->assertRedirect(route('nutrition'));
    $response->assertSessionHasErrors('macros');
    $goal->refresh();

    expect($goal->daily_calories)->toBe(2230)
        ->and(Macronutrient::query()->findOrFail($goal->macronutrient_id)->only(['protein', 'carbs', 'fats']))
        ->toBe($originalMacros);
})->with([
    'free' => ['Gratuit'],
    'plus' => ['Plus'],
]);
