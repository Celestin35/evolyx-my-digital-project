<?php

namespace Database\Seeders\Demo\MainUser;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoWorkoutSessionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $userId = DB::table('users')->where('email', 'demo@evolyx.local')->value('id');

        if (! $userId) {
            return;
        }

        $this->clearExistingWorkoutData($userId);
        $templateIds = $this->createWorkoutTemplates($userId, $now);
        $this->createPerformedSessions($userId, $templateIds, $now);
    }

    private function clearExistingWorkoutData(int $userId): void
    {
        DB::table('community_posts')->where('user_id', $userId)->delete();
        DB::table('performances')->where('user_id', $userId)->delete();
        DB::table('performed_sessions')->where('user_id', $userId)->delete();
        DB::table('workout_sessions')->where('user_id', $userId)->delete();
    }

    private function createWorkoutTemplates(int $userId, $now): array
    {
        $exerciseIds = $this->exerciseIdsBySport();
        $templates = [
            'Développé couché progression' => [
                'description' => 'Séance de suivi sur le développé couché.',
                'sport' => 'Fitness / musculation',
                'exercise' => 'Développé couché',
            ],
            'Squat progression' => [
                'description' => 'Séance de suivi sur le squat.',
                'sport' => 'Fitness / musculation',
                'exercise' => 'Squat',
            ],
            'Course endurance progression' => [
                'description' => 'Sortie de suivi en endurance fondamentale.',
                'sport' => 'Course à pied / running',
                'exercise' => 'Course en endurance fondamentale',
            ],
        ];

        $templateIds = [];

        foreach ($templates as $name => $template) {
            $workoutSessionId = DB::table('workout_sessions')->insertGetId([
                'user_id' => $userId,
                'name' => $name,
                'description' => $template['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $exerciseId = $exerciseIds[$template['sport'].'|'.$template['exercise']] ?? null;

            if ($exerciseId) {
                DB::table('workout_session_exercise')->insert([
                    'workout_session_id' => $workoutSessionId,
                    'exercise_id' => $exerciseId,
                    'rest_time' => null,
                    'position' => 1,
                ]);
            }

            $templateIds[$name] = $workoutSessionId;
        }

        return $templateIds;
    }

    private function createPerformedSessions(int $userId, array $templateIds, $now): void
    {
        $sessions = [
            ['Course endurance progression', -57, 45, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 42, 'distance_meters' => 6600, 'pace_min_per_km' => 6.36, 'heart_rate_bpm' => 148, 'perceived_effort' => 5]],
            ['Développé couché progression', -54, 55, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 42.5, 'perceived_effort' => 7]],
            ['Squat progression', -51, 62, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 57.5, 'perceived_effort' => 7]],
            ['Course endurance progression', -49, 48, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 44, 'distance_meters' => 7000, 'pace_min_per_km' => 6.29, 'heart_rate_bpm' => 146, 'perceived_effort' => 5]],
            ['Développé couché progression', -46, 56, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 45, 'perceived_effort' => 7]],
            ['Squat progression', -43, 63, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 60, 'perceived_effort' => 7]],
            ['Course endurance progression', -41, 50, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 46, 'distance_meters' => 7350, 'pace_min_per_km' => 6.26, 'heart_rate_bpm' => 145, 'perceived_effort' => 5]],
            ['Développé couché progression', -38, 57, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 44, 'perceived_effort' => 8]],
            ['Course endurance progression', -35, 52, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 47, 'distance_meters' => 7600, 'pace_min_per_km' => 6.18, 'heart_rate_bpm' => 144, 'perceived_effort' => 5]],
            ['Squat progression', -33, 64, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 62.5, 'perceived_effort' => 8]],
            ['Développé couché progression', -30, 58, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 47.5, 'perceived_effort' => 7]],
            ['Course endurance progression', -28, 53, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 48, 'distance_meters' => 7900, 'pace_min_per_km' => 6.08, 'heart_rate_bpm' => 143, 'perceived_effort' => 5]],
            ['Développé couché progression', -25, 58, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 46.5, 'perceived_effort' => 8]],
            ['Squat progression', -23, 65, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 61, 'perceived_effort' => 8]],
            ['Course endurance progression', -21, 54, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 49, 'distance_meters' => 8150, 'pace_min_per_km' => 6.01, 'heart_rate_bpm' => 143, 'perceived_effort' => 5]],
            ['Développé couché progression', -18, 59, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 49, 'perceived_effort' => 8]],
            ['Squat progression', -16, 66, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 64, 'perceived_effort' => 8]],
            ['Course endurance progression', -14, 56, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 50, 'distance_meters' => 8400, 'pace_min_per_km' => 5.95, 'heart_rate_bpm' => 142, 'perceived_effort' => 5]],
            ['Développé couché progression', -12, 58, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 48, 'perceived_effort' => 8]],
            ['Course endurance progression', -10, 57, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 51, 'distance_meters' => 8550, 'pace_min_per_km' => 5.96, 'heart_rate_bpm' => 144, 'perceived_effort' => 6]],
            ['Squat progression', -8, 67, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 66, 'perceived_effort' => 8]],
            ['Développé couché progression', -6, 60, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 50, 'perceived_effort' => 8]],
            ['Course endurance progression', -5, 58, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 52, 'distance_meters' => 8800, 'pace_min_per_km' => 5.91, 'heart_rate_bpm' => 142, 'perceived_effort' => 5]],
            ['Développé couché progression', -4, 60, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 51, 'perceived_effort' => 9]],
        ];

        foreach ($sessions as [$templateName, $dayOffset, $durationMinutes, $performance]) {
            $performedAt = $now->copy()->addDays($dayOffset)->setTime(18, 0);
            $performedSessionId = DB::table('performed_sessions')->insertGetId([
                'user_id' => $userId,
                'workout_session_id' => $templateIds[$templateName],
                'performed_at' => $performedAt,
                'completed_at' => $performedAt->copy()->addMinutes($durationMinutes),
                'notes' => 'Donnée de démonstration pour les graphiques de progression.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->createPerformance($userId, $performedSessionId, $performedAt, $performance, $now);
        }
    }

    private function createPerformance(int $userId, int $performedSessionId, $performedAt, array $data, $now): void
    {
        $exerciseIds = $this->exerciseIdsBySport();
        $exerciseId = $exerciseIds[$data['sport'].'|'.$data['exercise']] ?? null;

        if (! $exerciseId) {
            return;
        }

        $performanceId = DB::table('performances')->insertGetId([
            'performed_session_id' => $performedSessionId,
            'exercise_id' => $exerciseId,
            'user_id' => $userId,
            'performed_at' => $performedAt,
            'weight' => $data['weight'] ?? null,
            'repetitions' => $data['repetitions'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'distance_meters' => $data['distance_meters'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $metricIds = DB::table('metrics')->pluck('id', 'key');
        $metricValues = [
            'sets' => $data['sets'] ?? null,
            'repetitions' => $data['repetitions'] ?? null,
            'weight_kg' => $data['weight'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'distance_meters' => $data['distance_meters'] ?? null,
            'pace_min_per_km' => $data['pace_min_per_km'] ?? null,
            'heart_rate_bpm' => $data['heart_rate_bpm'] ?? null,
            'perceived_effort' => $data['perceived_effort'] ?? null,
        ];

        foreach ($metricValues as $metricKey => $value) {
            if ($value === null || ! isset($metricIds[$metricKey])) {
                continue;
            }

            DB::table('performance_metric_values')->insert([
                'performance_id' => $performanceId,
                'metric_id' => $metricIds[$metricKey],
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function exerciseIdsBySport(): array
    {
        $sports = DB::table('sports')->pluck('id', 'name');
        $sportNamesById = $sports->flip();

        return DB::table('exercises')
            ->whereNull('user_id')
            ->get(['id', 'name', 'sport_id'])
            ->mapWithKeys(fn (object $exercise) => [
                $sportNamesById[$exercise->sport_id].'|'.$exercise->name => $exercise->id,
            ])
            ->all();
    }
}
