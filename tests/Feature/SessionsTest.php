<?php

use App\Models\Performance;
use App\Models\PerformanceMetricValue;
use App\Models\WorkoutSession;

test('a user can create a workout session', function () {
    $sport = evolyxSport();
    $user = evolyxUserWithSport($sport);
    $exercise = evolyxExercise($user, $sport);

    $response = $this
        ->actingAs($user)
        ->post(route('sessions.workout-sessions.store'), [
            'name' => 'Push maison',
            'description' => 'Pecs et épaules',
            'exercise_ids' => [$exercise->id],
        ]);

    $response->assertRedirect(route('sessions'));
    $this->assertDatabaseHas('workout_sessions', [
        'user_id' => $user->id,
        'name' => 'Push maison',
    ]);

    $workoutSession = WorkoutSession::query()->where('name', 'Push maison')->firstOrFail();
    $this->assertDatabaseHas('workout_session_exercise', [
        'workout_session_id' => $workoutSession->id,
        'exercise_id' => $exercise->id,
        'position' => 1,
    ]);
});

test('a user can update their own workout session', function () {
    $sport = evolyxSport();
    $user = evolyxUserWithSport($sport);
    $exercise = evolyxExercise($user, $sport);
    $workoutSession = evolyxWorkoutSession($user, $exercise);

    $response = $this
        ->actingAs($user)
        ->patch(route('sessions.workout-sessions.update', $workoutSession), [
            'name' => 'Push modifiée',
            'description' => 'Nouvelle description',
            'exercise_ids' => [$exercise->id],
        ]);

    $response->assertRedirect(route('sessions'));
    $this->assertDatabaseHas('workout_sessions', [
        'id' => $workoutSession->id,
        'name' => 'Push modifiée',
        'description' => 'Nouvelle description',
    ]);
});

test('a user cannot update another user workout session', function () {
    $sport = evolyxSport();
    $owner = evolyxUserWithSport($sport);
    $otherUser = evolyxUserWithSport($sport);
    $exercise = evolyxExercise($owner, $sport);
    $otherExercise = evolyxExercise($otherUser, $sport);
    $workoutSession = evolyxWorkoutSession($owner, $exercise);

    $response = $this
        ->actingAs($otherUser)
        ->patch(route('sessions.workout-sessions.update', $workoutSession), [
            'name' => 'Tentative',
            'description' => null,
            'exercise_ids' => [$otherExercise->id],
        ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('workout_sessions', [
        'id' => $workoutSession->id,
        'name' => 'Séance haut du corps',
    ]);
});

test('a user can schedule a performed session', function () {
    $sport = evolyxSport();
    $user = evolyxUserWithSport($sport);
    $workoutSession = evolyxWorkoutSession($user, evolyxExercise($user, $sport));

    $response = $this
        ->actingAs($user)
        ->post(route('sessions.performed-sessions.store'), [
            'workout_session_id' => $workoutSession->id,
            'performed_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'notes' => 'Prévue demain',
        ]);

    $response->assertRedirect(route('sessions'));
    $this->assertDatabaseHas('performed_sessions', [
        'user_id' => $user->id,
        'workout_session_id' => $workoutSession->id,
        'notes' => 'Prévue demain',
    ]);
});

test('a user can complete a performed session with valid dynamic metrics', function () {
    $sport = evolyxSport();
    $user = evolyxUserWithSport($sport);
    $weightMetric = evolyxMetric('weight_kg', 'Charge', 'kg');
    $repetitionMetric = evolyxMetric('repetitions', 'Répétitions', 'rep', 'integer', 2);
    $exercise = evolyxExercise($user, $sport, [$weightMetric, $repetitionMetric]);
    $performedSession = evolyxPerformedSession($user, evolyxWorkoutSession($user, $exercise));

    $response = $this
        ->actingAs($user)
        ->patch(route('sessions.performed-sessions.complete', $performedSession), [
            'notes' => 'Validée',
            'performances' => [
                [
                    'exercise_id' => $exercise->id,
                    'metrics' => [
                        'weight_kg' => 82.5,
                        'repetitions' => 8,
                    ],
                ],
            ],
        ]);

    $response->assertRedirect(route('sessions'));
    $performedSession->refresh();
    expect($performedSession->completed_at)->not->toBeNull();

    $performance = Performance::query()
        ->where('performed_session_id', $performedSession->id)
        ->where('exercise_id', $exercise->id)
        ->firstOrFail();

    $this->assertDatabaseHas('performances', [
        'id' => $performance->id,
        'performed_session_id' => $performedSession->id,
        'exercise_id' => $exercise->id,
        'user_id' => $user->id,
    ]);
    $this->assertDatabaseHas('performance_metric_values', [
        'performance_id' => $performance->id,
        'metric_id' => $weightMetric->id,
        'value' => 82.5,
    ]);
    $this->assertDatabaseHas('performance_metric_values', [
        'performance_id' => $performance->id,
        'metric_id' => $repetitionMetric->id,
        'value' => 8,
    ]);
});

test('completion rejects invalid dynamic metrics', function () {
    $sport = evolyxSport();
    $user = evolyxUserWithSport($sport);
    $exercise = evolyxExercise($user, $sport, [
        evolyxMetric('weight_kg', 'Charge', 'kg'),
    ]);
    $performedSession = evolyxPerformedSession($user, evolyxWorkoutSession($user, $exercise));

    $response = $this
        ->actingAs($user)
        ->patch(route('sessions.performed-sessions.complete', $performedSession), [
            'performances' => [
                [
                    'exercise_id' => $exercise->id,
                    'metrics' => [
                        'invalid_metric' => 12,
                    ],
                ],
            ],
        ]);

    $response->assertSessionHasErrors('performances');
    $this->assertDatabaseMissing('performances', [
        'performed_session_id' => $performedSession->id,
        'exercise_id' => $exercise->id,
    ]);
    expect(PerformanceMetricValue::query()->count())->toBe(0);
});
