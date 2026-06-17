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
        $this->createWorkoutTemplates($userId, $now);
        $this->createPerformedSessions($userId, $now);
    }

    private function clearExistingWorkoutData(int $userId): void
    {
        DB::table('community_posts')->where('user_id', $userId)->delete();
        DB::table('performances')->where('user_id', $userId)->delete();
        DB::table('performed_sessions')->where('user_id', $userId)->delete();
        DB::table('workout_sessions')->where('user_id', $userId)->delete();
    }

    private function createWorkoutTemplates(int $userId, $now): void
    {
        $exerciseIds = DB::table('exercises')->pluck('id', 'name');
        $templates = [
            'Push force' => [
                'description' => 'Pectoraux, epaules et triceps.',
                'exercises' => ['Développé couché', 'Développé militaire', 'Tractions'],
            ],
            'Jambes controle' => [
                'description' => 'Jambes et gainage.',
                'exercises' => ['Squat', 'Presse à cuisses', 'Gainage'],
            ],
            'Running endurance' => [
                'description' => 'Sortie de base en endurance.',
                'exercises' => ['Footing endurance', 'Récupération active running'],
            ],
            'Running vitesse' => [
                'description' => 'Travail allure et fractionne.',
                'exercises' => ['Fractionné court', 'Tempo run'],
            ],
        ];

        foreach ($templates as $name => $template) {
            DB::table('workout_sessions')->updateOrInsert(
                ['user_id' => $userId, 'name' => $name],
                [
                    'description' => $template['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );

            $workoutSessionId = DB::table('workout_sessions')
                ->where('user_id', $userId)
                ->where('name', $name)
                ->value('id');

            foreach ($template['exercises'] as $position => $exerciseName) {
                $exerciseId = $exerciseIds[$exerciseName] ?? null;

                if (! $exerciseId) {
                    continue;
                }

                DB::table('workout_session_exercise')->updateOrInsert(
                    [
                        'workout_session_id' => $workoutSessionId,
                        'exercise_id' => $exerciseId,
                    ],
                    [
                        'rest_time' => null,
                        'position' => $position + 1,
                    ],
                );
            }
        }
    }

    private function createPerformedSessions(int $userId, $now): void
    {
        $sessions = DB::table('workout_sessions')
            ->where('user_id', $userId)
            ->pluck('id', 'name');

        $schedule = [
            ['Push force', -12, true, 'Seance faite ce mois-ci.'],
            ['Running endurance', -8, true, 'Sortie endurance terminee.'],
            ['Jambes controle', -3, true, 'Seance jambes validee.'],
            ['Running vitesse', -1, false, 'Seance pas encore validee.'],
            ['Push force', 2, false, 'Seance prevue cette semaine.'],
            ['Running endurance', 4, false, 'Sortie prevue cette semaine.'],
            ['Jambes controle', 6, false, 'Seance prevue cette semaine.'],
        ];

        foreach ($schedule as [$sessionName, $dayOffset, $isCompleted, $notes]) {
            $performedAt = $now->copy()->addDays($dayOffset)->setTime(18, 0);

            DB::table('performed_sessions')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'workout_session_id' => $sessions[$sessionName],
                    'performed_at' => $performedAt,
                ],
                [
                    'completed_at' => $isCompleted ? $performedAt->copy()->addMinutes(65) : null,
                    'notes' => $notes,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }
}
