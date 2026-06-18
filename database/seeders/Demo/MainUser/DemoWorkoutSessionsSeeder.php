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
            'Musculation - Push force' => [
                'description' => 'Pectoraux, épaules et triceps.',
                'sport' => 'Fitness / musculation',
                'exercises' => ['Développé couché', 'Développé militaire', 'Extension triceps'],
            ],
            'Musculation - Jambes force' => [
                'description' => 'Jambes et chaîne postérieure.',
                'sport' => 'Fitness / musculation',
                'exercises' => ['Squat', 'Soulevé de terre', 'Presse à cuisses'],
            ],
            'Musculation - Tirage' => [
                'description' => 'Dos et biceps.',
                'sport' => 'Fitness / musculation',
                'exercises' => ['Tractions', 'Rowing barre', 'Curl biceps'],
            ],
            'Course - Endurance' => [
                'description' => 'Sortie régulière en endurance fondamentale.',
                'sport' => 'Course à pied / running',
                'exercises' => ['Course en endurance fondamentale'],
            ],
            'Course - Tempo' => [
                'description' => 'Sortie soutenue pour travailler l’allure.',
                'sport' => 'Course à pied / running',
                'exercises' => ['Course tempo'],
            ],
            'Course - Fractionné' => [
                'description' => 'Travail de vitesse avec répétitions.',
                'sport' => 'Course à pied / running',
                'exercises' => ['Fractionné court'],
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

            foreach ($template['exercises'] as $position => $exerciseName) {
                $exerciseId = $exerciseIds[$template['sport'].'|'.$exerciseName] ?? null;

                if (! $exerciseId) {
                    continue;
                }

                DB::table('workout_session_exercise')->insert([
                    'workout_session_id' => $workoutSessionId,
                    'exercise_id' => $exerciseId,
                    'rest_time' => null,
                    'position' => $position + 1,
                ]);
            }

            $templateIds[$name] = $workoutSessionId;
        }

        return $templateIds;
    }

    private function createPerformedSessions(int $userId, array $templateIds, $now): void
    {
        $sessions = [
            ['Course - Endurance', -55, 48, [
                ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 42, 'distance_meters' => 6800, 'pace_min_per_km' => 6.18, 'heart_rate_bpm' => 146, 'perceived_effort' => 5],
            ]],
            ['Musculation - Push force', -52, 62, [
                ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 42.5, 'perceived_effort' => 7],
                ['sport' => 'Fitness / musculation', 'exercise' => 'Développé militaire', 'sets' => 3, 'repetitions' => 8, 'weight' => 24, 'perceived_effort' => 7],
            ]],
            ['Course - Tempo', -49, 45, [
                ['sport' => 'Course à pied / running', 'exercise' => 'Course tempo', 'duration_minutes' => 28, 'distance_meters' => 5000, 'pace_min_per_km' => 5.60, 'heart_rate_bpm' => 158, 'perceived_effort' => 7],
            ]],
            ['Musculation - Jambes force', -46, 68, [
                ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 58, 'perceived_effort' => 7],
                ['sport' => 'Fitness / musculation', 'exercise' => 'Soulevé de terre', 'sets' => 3, 'repetitions' => 6, 'weight' => 66, 'perceived_effort' => 8],
            ]],
            ['Course - Fractionné', -43, 38, [
                ['sport' => 'Course à pied / running', 'exercise' => 'Fractionné court', 'sets' => 8, 'distance_meters' => 3200, 'pace_min_per_km' => 5.20, 'heart_rate_bpm' => 166, 'perceived_effort' => 8],
            ]],
            ['Musculation - Tirage', -40, 58, [
                ['sport' => 'Fitness / musculation', 'exercise' => 'Tractions', 'sets' => 4, 'repetitions' => 6, 'weight' => 0, 'perceived_effort' => 8],
                ['sport' => 'Fitness / musculation', 'exercise' => 'Rowing barre', 'sets' => 4, 'repetitions' => 9, 'weight' => 38, 'perceived_effort' => 7],
            ]],
            ['Course - Endurance', -37, 52, [
                ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 45, 'distance_meters' => 7400, 'pace_min_per_km' => 6.08, 'heart_rate_bpm' => 144, 'perceived_effort' => 5],
            ]],
            ['Musculation - Push force', -34, 64, [
                ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 45, 'perceived_effort' => 7],
                ['sport' => 'Fitness / musculation', 'exercise' => 'Développé militaire', 'sets' => 3, 'repetitions' => 8, 'weight' => 25, 'perceived_effort' => 7],
            ]],
            ['Course - Tempo', -31, 47, [
                ['sport' => 'Course à pied / running', 'exercise' => 'Course tempo', 'duration_minutes' => 30, 'distance_meters' => 5450, 'pace_min_per_km' => 5.50, 'heart_rate_bpm' => 157, 'perceived_effort' => 7],
            ]],
            ['Musculation - Jambes force', -28, 70, [
                ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 61, 'perceived_effort' => 7],
                ['sport' => 'Fitness / musculation', 'exercise' => 'Soulevé de terre', 'sets' => 3, 'repetitions' => 6, 'weight' => 70, 'perceived_effort' => 8],
            ]],
            ['Course - Endurance', -25, 55, [
                ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 48, 'distance_meters' => 8000, 'pace_min_per_km' => 6.00, 'heart_rate_bpm' => 143, 'perceived_effort' => 5],
            ]],
            ['Musculation - Tirage', -22, 60, [
                ['sport' => 'Fitness / musculation', 'exercise' => 'Tractions', 'sets' => 4, 'repetitions' => 7, 'weight' => 0, 'perceived_effort' => 8],
                ['sport' => 'Fitness / musculation', 'exercise' => 'Rowing barre', 'sets' => 4, 'repetitions' => 9, 'weight' => 41, 'perceived_effort' => 7],
            ]],
            ['Course - Fractionné', -19, 40, [
                ['sport' => 'Course à pied / running', 'exercise' => 'Fractionné court', 'sets' => 9, 'distance_meters' => 3600, 'pace_min_per_km' => 5.08, 'heart_rate_bpm' => 168, 'perceived_effort' => 8],
            ]],
            ['Musculation - Push force', -16, 65, [
                ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 47.5, 'perceived_effort' => 8],
                ['sport' => 'Fitness / musculation', 'exercise' => 'Développé militaire', 'sets' => 3, 'repetitions' => 8, 'weight' => 26, 'perceived_effort' => 7],
            ]],
            ['Course - Tempo', -13, 48, [
                ['sport' => 'Course à pied / running', 'exercise' => 'Course tempo', 'duration_minutes' => 32, 'distance_meters' => 5950, 'pace_min_per_km' => 5.38, 'heart_rate_bpm' => 156, 'perceived_effort' => 7],
            ]],
            ['Musculation - Jambes force', -11, 72, [
                ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 64, 'perceived_effort' => 8],
                ['sport' => 'Fitness / musculation', 'exercise' => 'Soulevé de terre', 'sets' => 3, 'repetitions' => 6, 'weight' => 74, 'perceived_effort' => 8],
            ]],
            ['Course - Endurance', -9, 58, [
                ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 50, 'distance_meters' => 8500, 'pace_min_per_km' => 5.88, 'heart_rate_bpm' => 142, 'perceived_effort' => 5],
            ]],
            ['Musculation - Tirage', -7, 62, [
                ['sport' => 'Fitness / musculation', 'exercise' => 'Tractions', 'sets' => 4, 'repetitions' => 8, 'weight' => 0, 'perceived_effort' => 8],
                ['sport' => 'Fitness / musculation', 'exercise' => 'Rowing barre', 'sets' => 4, 'repetitions' => 9, 'weight' => 44, 'perceived_effort' => 7],
            ]],
            ['Course - Fractionné', -5, 42, [
                ['sport' => 'Course à pied / running', 'exercise' => 'Fractionné court', 'sets' => 10, 'distance_meters' => 4000, 'pace_min_per_km' => 4.98, 'heart_rate_bpm' => 169, 'perceived_effort' => 8],
            ]],
            ['Musculation - Push force', -4, 66, [
                ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 50, 'perceived_effort' => 8],
                ['sport' => 'Fitness / musculation', 'exercise' => 'Développé militaire', 'sets' => 3, 'repetitions' => 8, 'weight' => 27.5, 'perceived_effort' => 8],
            ]],
        ];

        foreach ($sessions as [$templateName, $dayOffset, $durationMinutes, $performances]) {
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

            foreach ($performances as $performance) {
                $this->createPerformance($userId, $performedSessionId, $performedAt, $performance, $now);
            }
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
