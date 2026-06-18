<?php

namespace Database\Seeders\Sports;

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
            ->keyBy(fn (object $exercise) => $exercise->sport_id.'|'.$exercise->name)
            ->all();
        $catalogSessionKeys = collect($catalog)
            ->flatMap(function (array $sportTemplate) use ($sports) {
                $sportId = $sports[$sportTemplate['sport']] ?? null;

                return collect($sportTemplate['sessions'])
                    ->map(fn (array $sessionTemplate) => $sportId.'|'.$sessionTemplate['name']);
            })
            ->flip();

        $this->deleteStaleSystemSessions($catalogSessionKeys);

        foreach ($catalog as $sportTemplate) {
            $sportId = $sports[$sportTemplate['sport']] ?? null;

            if (! $sportId) {
                continue;
            }

            foreach ($sportTemplate['sessions'] as $sessionTemplate) {
                $workoutSessionId = DB::table('workout_sessions')
                    ->whereNull('user_id')
                    ->where('name', $sessionTemplate['name'])
                    ->whereExists(function ($query) use ($sportId) {
                        $query->select(DB::raw(1))
                            ->from('workout_session_exercise')
                            ->join('exercises', 'workout_session_exercise.exercise_id', '=', 'exercises.id')
                            ->whereColumn('workout_session_exercise.workout_session_id', 'workout_sessions.id')
                            ->where('exercises.sport_id', $sportId);
                    })
                    ->value('id');

                if (! $workoutSessionId) {
                    $workoutSessionId = DB::table('workout_sessions')->insertGetId(
                        [
                            'user_id' => null,
                            'name' => $sessionTemplate['name'],
                            'description' => $sessionTemplate['description'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                    );
                }

                DB::table('workout_sessions')
                    ->where('id', $workoutSessionId)
                    ->update(
                        [
                            'description' => $sessionTemplate['description'],
                            'updated_at' => $now,
                        ],
                    );

                $pivotRows = [];
                foreach ($sessionTemplate['exercises'] as $index => $exerciseName) {
                    $exercise = $exercises[$sportId.'|'.$exerciseName] ?? null;

                    if (! $exercise) {
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

                DB::table('workout_session_exercise')
                    ->where('workout_session_id', $workoutSessionId)
                    ->delete();

                DB::table('workout_session_exercise')->insert($pivotRows);
            }
        }

        $this->deleteStaleSystemSessions($catalogSessionKeys);
    }

    private function deleteStaleSystemSessions($catalogSessionKeys): void
    {
        $sessions = DB::table('workout_sessions')
            ->whereNull('user_id')
            ->get(['id', 'name']);

        foreach ($sessions as $session) {
            $sportIds = DB::table('workout_session_exercise')
                ->join('exercises', 'workout_session_exercise.exercise_id', '=', 'exercises.id')
                ->where('workout_session_exercise.workout_session_id', $session->id)
                ->distinct()
                ->pluck('exercises.sport_id');

            if ($sportIds->isEmpty()) {
                DB::table('workout_sessions')->where('id', $session->id)->delete();

                continue;
            }

            $isCurrentCatalogSession = $sportIds
                ->contains(fn ($sportId) => $catalogSessionKeys->has($sportId.'|'.$session->name));

            if (! $isCurrentCatalogSession) {
                DB::table('workout_sessions')->where('id', $session->id)->delete();
            }
        }
    }
}
