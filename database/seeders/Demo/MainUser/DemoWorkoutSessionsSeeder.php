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
        $this->createPerformedSessions($userId, $now);
    }

    private function clearExistingWorkoutData(int $userId): void
    {
        DB::table('community_posts')->where('user_id', $userId)->delete();
        DB::table('performances')->where('user_id', $userId)->delete();
        DB::table('performed_sessions')->where('user_id', $userId)->delete();
        DB::table('workout_sessions')->where('user_id', $userId)->delete();
    }

    private function createPerformedSessions(int $userId, $now): void
    {
        $sessions = [
            ['Course à pied / running', 'Endurance fondamentale 45 min', -146, 47, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 41, 'distance_meters' => 6350, 'pace_min_per_km' => 6.46, 'heart_rate_bpm' => 150, 'perceived_effort' => 5]],
            ['Fitness / musculation', 'Pectoraux et triceps', -142, 58, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 40, 'perceived_effort' => 7]],
            ['Fitness / musculation', 'Jambes force', -138, 64, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 55, 'perceived_effort' => 7]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -133, 48, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 42, 'distance_meters' => 6600, 'pace_min_per_km' => 6.36, 'heart_rate_bpm' => 148, 'perceived_effort' => 5]],
            ['Fitness / musculation', 'Pectoraux et triceps', -129, 58, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 42.5, 'perceived_effort' => 7]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -124, 50, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 43, 'distance_meters' => 6850, 'pace_min_per_km' => 6.28, 'heart_rate_bpm' => 147, 'perceived_effort' => 5]],
            ['Fitness / musculation', 'Jambes force', -120, 65, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 57.5, 'perceived_effort' => 7]],
            ['Fitness / musculation', 'Pectoraux et triceps', -116, 59, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 44, 'perceived_effort' => 7]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -111, 51, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 44, 'distance_meters' => 7100, 'pace_min_per_km' => 6.20, 'heart_rate_bpm' => 146, 'perceived_effort' => 5]],
            ['Fitness / musculation', 'Pectoraux et triceps', -106, 57, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 43, 'perceived_effort' => 8]],
            ['Fitness / musculation', 'Jambes force', -102, 66, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 60, 'perceived_effort' => 7]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -97, 52, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 45, 'distance_meters' => 7350, 'pace_min_per_km' => 6.12, 'heart_rate_bpm' => 145, 'perceived_effort' => 5]],
            ['Fitness / musculation', 'Pectoraux et triceps', -92, 60, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 45, 'perceived_effort' => 7]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -88, 52, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 46, 'distance_meters' => 7550, 'pace_min_per_km' => 6.09, 'heart_rate_bpm' => 144, 'perceived_effort' => 5]],
            ['Fitness / musculation', 'Jambes force', -83, 67, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 62.5, 'perceived_effort' => 8]],
            ['Fitness / musculation', 'Pectoraux et triceps', -79, 60, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 46.5, 'perceived_effort' => 8]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -74, 54, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 47, 'distance_meters' => 7800, 'pace_min_per_km' => 6.03, 'heart_rate_bpm' => 143, 'perceived_effort' => 5]],
            ['Fitness / musculation', 'Pectoraux et triceps', -69, 58, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 45.5, 'perceived_effort' => 8]],
            ['Fitness / musculation', 'Jambes force', -65, 66, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 61, 'perceived_effort' => 8]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -60, 55, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 48, 'distance_meters' => 8000, 'pace_min_per_km' => 6.00, 'heart_rate_bpm' => 143, 'perceived_effort' => 5]],
            ['Fitness / musculation', 'Pectoraux et triceps', -55, 61, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 48, 'perceived_effort' => 8]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -51, 56, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 49, 'distance_meters' => 8200, 'pace_min_per_km' => 5.98, 'heart_rate_bpm' => 142, 'perceived_effort' => 5]],
            ['Fitness / musculation', 'Jambes force', -46, 68, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 64, 'perceived_effort' => 8]],
            ['Fitness / musculation', 'Pectoraux et triceps', -42, 60, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 47, 'perceived_effort' => 8]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -37, 57, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 50, 'distance_meters' => 8350, 'pace_min_per_km' => 5.99, 'heart_rate_bpm' => 144, 'perceived_effort' => 6]],
            ['Fitness / musculation', 'Pectoraux et triceps', -32, 61, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 49, 'perceived_effort' => 8]],
            ['Fitness / musculation', 'Jambes force', -29, 69, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 66, 'perceived_effort' => 8]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -25, 58, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 51, 'distance_meters' => 8600, 'pace_min_per_km' => 5.93, 'heart_rate_bpm' => 142, 'perceived_effort' => 5]],
            ['Fitness / musculation', 'Pectoraux et triceps', -21, 59, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 48, 'perceived_effort' => 8]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -18, 59, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 52, 'distance_meters' => 8750, 'pace_min_per_km' => 5.94, 'heart_rate_bpm' => 143, 'perceived_effort' => 6]],
            ['Fitness / musculation', 'Jambes force', -15, 70, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 65, 'perceived_effort' => 9]],
            ['Fitness / musculation', 'Pectoraux et triceps', -12, 62, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 50, 'perceived_effort' => 8]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -9, 60, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 53, 'distance_meters' => 9000, 'pace_min_per_km' => 5.89, 'heart_rate_bpm' => 141, 'perceived_effort' => 5]],
            ['Fitness / musculation', 'Jambes force', -7, 71, ['sport' => 'Fitness / musculation', 'exercise' => 'Squat', 'sets' => 4, 'repetitions' => 8, 'weight' => 68, 'perceived_effort' => 9]],
            ['Fitness / musculation', 'Pectoraux et triceps', -5, 62, ['sport' => 'Fitness / musculation', 'exercise' => 'Développé couché', 'sets' => 4, 'repetitions' => 8, 'weight' => 51, 'perceived_effort' => 9]],
            ['Course à pied / running', 'Endurance fondamentale 45 min', -4, 61, ['sport' => 'Course à pied / running', 'exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 54, 'distance_meters' => 9150, 'pace_min_per_km' => 5.90, 'heart_rate_bpm' => 142, 'perceived_effort' => 5]],
        ];

        $systemSessionIds = $this->systemSessionIdsBySportAndName();

        foreach ($sessions as [$sportName, $sessionName, $dayOffset, $durationMinutes, $performance]) {
            $workoutSessionId = $systemSessionIds[$sportName.'|'.$sessionName] ?? null;

            if (! $workoutSessionId) {
                continue;
            }

            $performedAt = $now->copy()->addDays($dayOffset)->setTime(18, 0);
            $performedSessionId = DB::table('performed_sessions')->insertGetId([
                'user_id' => $userId,
                'workout_session_id' => $workoutSessionId,
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

    private function systemSessionIdsBySportAndName(): array
    {
        $sports = DB::table('sports')->pluck('id', 'name');
        $sportNamesById = $sports->flip();
        $sessionIds = [];

        $sessions = DB::table('workout_sessions')
            ->whereNull('user_id')
            ->get(['id', 'name']);

        foreach ($sessions as $session) {
            $sportIds = DB::table('workout_session_exercise')
                ->join('exercises', 'workout_session_exercise.exercise_id', '=', 'exercises.id')
                ->where('workout_session_exercise.workout_session_id', $session->id)
                ->distinct()
                ->pluck('exercises.sport_id');

            foreach ($sportIds as $sportId) {
                $sportName = $sportNamesById[$sportId] ?? null;

                if ($sportName) {
                    $sessionIds[$sportName.'|'.$session->name] = $session->id;
                }
            }
        }

        return $sessionIds;
    }
}
