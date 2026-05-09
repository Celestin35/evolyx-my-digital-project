<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkoutSessionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $catalog = require database_path('seeders/data/workout_session_catalog.php');
        $sports = DB::table('sports')->pluck('id', 'name');
        $exercises = DB::table('exercises')
            ->whereNull('user_id')
            ->get(['id', 'name', 'sport_id'])
            ->keyBy('name');

        foreach ($catalog as $sportTemplate) {
            $sportId = $sports[$sportTemplate['sport']] ?? null;

            if (! $sportId) {
                continue;
            }

            foreach ($sportTemplate['sessions'] as $sessionTemplate) {
                DB::table('workout_sessions')->updateOrInsert(
                    ['user_id' => null, 'name' => $sessionTemplate['name']],
                    [
                        'description' => $sessionTemplate['description'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );

                $workoutSessionId = DB::table('workout_sessions')
                    ->whereNull('user_id')
                    ->where('name', $sessionTemplate['name'])
                    ->value('id');

                $pivotRows = [];
                foreach ($sessionTemplate['exercises'] as $index => $exerciseName) {
                    $exercise = $exercises[$exerciseName] ?? null;

                    if (! $exercise || (int) $exercise->sport_id !== (int) $sportId) {
                        continue;
                    }

                    $pivotRows[] = [
                        'workout_session_id' => $workoutSessionId,
                        'exercise_id' => $exercise->id,
                        'rest_time' => null,
                        'position' => $index + 1,
                    ];
                }

                if ($pivotRows === []) {
                    continue;
                }

                DB::table('workout_session_exercise')->upsert(
                    $pivotRows,
                    ['workout_session_id', 'exercise_id'],
                    ['rest_time', 'position'],
                );

                DB::table('workout_session_exercise')
                    ->where('workout_session_id', $workoutSessionId)
                    ->whereNotIn('exercise_id', collect($pivotRows)->pluck('exercise_id'))
                    ->delete();
            }
        }
    }
}
