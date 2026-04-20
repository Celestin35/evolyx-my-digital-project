<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $users = DB::table('users')->pluck('id', 'email');
        $plans = DB::table('subscription_plans')->pluck('id', 'name');
        $goalTypes = DB::table('goal_types')->pluck('id', 'name');
        $exercises = DB::table('exercises')->pluck('id', 'name');

        $macros = [
            [
                'fats' => 70,
                'carbs' => 230,
                'protein' => 140,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'fats' => 60,
                'carbs' => 180,
                'protein' => 120,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($macros as $macro) {
            DB::table('macronutrients')->updateOrInsert(
                [
                    'fats' => $macro['fats'],
                    'carbs' => $macro['carbs'],
                    'protein' => $macro['protein'],
                ],
                [
                    'created_at' => $macro['created_at'],
                    'updated_at' => $macro['updated_at'],
                ]
            );
        }

        $macroIds = DB::table('macronutrients')->pluck('id');

        DB::table('subscriptions')->updateOrInsert(
            ['user_id' => $users['demo@evolyx.local'], 'subscription_plan_id' => $plans['Premium']],
            [
                'start_date' => now()->subDays(30),
                'end_date' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('goals')->updateOrInsert(
            ['user_id' => $users['demo@evolyx.local'], 'is_active' => true],
            [
                'target_weight' => 61.50,
                'weekly_weight_goal' => 0.25,
                'daily_calories' => 2100,
                'goal_end_date' => now()->addDays(42)->toDateString(),
                'macronutrient_id' => $macroIds[0],
                'goal_type_id' => $goalTypes['Prise de masse'],
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $workoutSessions = [
            [
                'user_id' => $users['demo@evolyx.local'],
                'name' => 'Full body debutant',
                'description' => 'Seance equilibree orientee tonification et progression globale.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $users['demo@evolyx.local'],
                'name' => 'Mobilite du matin',
                'description' => 'Routine courte pour demarrer la journee avec plus d amplitude.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($workoutSessions as $session) {
            DB::table('workout_sessions')->updateOrInsert(
                ['user_id' => $session['user_id'], 'name' => $session['name']],
                [
                    'description' => $session['description'],
                    'created_at' => $session['created_at'],
                    'updated_at' => $session['updated_at'],
                ]
            );
        }

        $sessions = DB::table('workout_sessions')
            ->where('user_id', $users['demo@evolyx.local'])
            ->pluck('id', 'name');

        DB::table('workout_session_exercise')->upsert([
            [
                'workout_session_id' => $sessions['Full body debutant'],
                'exercise_id' => $exercises['Squat goblet'],
                'rest_time' => 90,
                'position' => 1,
            ],
            [
                'workout_session_id' => $sessions['Full body debutant'],
                'exercise_id' => $exercises['Developpe couche halteres'],
                'rest_time' => 90,
                'position' => 2,
            ],
            [
                'workout_session_id' => $sessions['Full body debutant'],
                'exercise_id' => $exercises['Rowing barre'],
                'rest_time' => 90,
                'position' => 3,
            ],
            [
                'workout_session_id' => $sessions['Mobilite du matin'],
                'exercise_id' => $exercises['Salutation au soleil'],
                'rest_time' => 30,
                'position' => 1,
            ],
            [
                'workout_session_id' => $sessions['Mobilite du matin'],
                'exercise_id' => $exercises['Ouverture thoracique'],
                'rest_time' => 30,
                'position' => 2,
            ],
            [
                'workout_session_id' => $sessions['Mobilite du matin'],
                'exercise_id' => $exercises['Respiration diaphragmatique'],
                'rest_time' => 15,
                'position' => 3,
            ],
        ], ['workout_session_id', 'exercise_id'], ['rest_time', 'position']);

        $performances = [
            [
                'performed_at' => now()->subDays(5),
                'weight' => 18.00,
                'repetitions' => 12,
                'duration_minutes' => null,
                'distance_meters' => null,
                'exercise_id' => $exercises['Squat goblet'],
                'user_id' => $users['demo@evolyx.local'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'performed_at' => now()->subDays(5),
                'weight' => 14.00,
                'repetitions' => 10,
                'duration_minutes' => null,
                'distance_meters' => null,
                'exercise_id' => $exercises['Developpe couche halteres'],
                'user_id' => $users['demo@evolyx.local'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'performed_at' => now()->subDays(2),
                'weight' => null,
                'repetitions' => null,
                'duration_minutes' => 25,
                'distance_meters' => 2400,
                'exercise_id' => $exercises['Marche active'],
                'user_id' => $users['demo@evolyx.local'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($performances as $performance) {
            DB::table('performances')->updateOrInsert(
                [
                    'user_id' => $performance['user_id'],
                    'exercise_id' => $performance['exercise_id'],
                    'performed_at' => $performance['performed_at'],
                ],
                [
                    'weight' => $performance['weight'],
                    'repetitions' => $performance['repetitions'],
                    'duration_minutes' => $performance['duration_minutes'],
                    'distance_meters' => $performance['distance_meters'],
                    'created_at' => $performance['created_at'],
                    'updated_at' => $performance['updated_at'],
                ]
            );
        }

        DB::table('security_logs')->updateOrInsert(
            [
                'user_id' => $users['admin@evolyx.local'],
                'action' => 'seed.login_check',
                'created_at' => $now->copy()->subMinute(),
            ],
            [
                'description' => 'Entree de demonstration creee par le seeder pour valider l affichage des journaux.',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder/Database',
            ]
        );
    }
}
