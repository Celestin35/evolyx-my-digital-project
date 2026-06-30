<?php

use App\Services\CaloriesCalculationService;
use Carbon\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

test('it calculates maintenance calories and macros deterministically', function () {
    // Arrange
    $service = new CaloriesCalculationService;
    $data = [
        'weight' => 80,
        'height' => 180,
        'age' => 30,
        'sex' => 'male',
        'activity_level' => 'moderate',
    ];

    // Act
    $result = $service->calculateForUser($data);

    // Assert
    expect($result)->toBe([
        'bmr' => 1780,
        'maintenance_calories' => 2759,
        'target_calories' => 2759,
        'macros' => [
            'protein' => 207,
            'fats' => 77,
            'carbs' => 310,
        ],
    ]);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('it calculates a weight loss goal plan with its duration and macros', function () {
    // Arrange
    Carbon::setTestNow('2026-01-01 12:00:00');
    $service = new CaloriesCalculationService;
    $data = [
        'current_weight' => 80,
        'target_weight' => 75,
        'weekly_weight_goal' => -0.5,
        'height' => 180,
        'age' => 30,
        'sex' => 'male',
        'activity_level' => 'moderate',
    ];

    // Act
    $result = $service->calculateGoalPlan($data);

    // Assert
    expect($result)
        ->toMatchArray([
            'bmr' => 1780,
            'maintenance_calories' => 2759,
            'target_calories' => 2209,
            'daily_calorie_adjustment' => 550,
            'weekly_weight_change' => -0.5,
            'days_remaining' => 70,
            'goal_end_date' => '2026-03-12',
            'is_realistic' => true,
            'macros' => [
                'protein' => 166,
                'fats' => 61,
                'carbs' => 249,
            ],
        ]);

});
