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
        $sports = DB::table('sports')->pluck('id', 'name');

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

        DB::table('sport_user')->updateOrInsert([
            'user_id' => $users['demo@evolyx.local'],
            'sport_id' => $sports['Musculation'],
        ]);

        $workoutSessions = [
            [
                'user_id' => $users['demo@evolyx.local'],
                'name' => 'Push force',
                'description' => 'Pectoraux, epaules et triceps avec priorite sur la progression en charge.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $users['demo@evolyx.local'],
                'name' => 'Pull dos biceps',
                'description' => 'Tirages horizontaux et verticaux pour suivre le dos et les bras.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $users['demo@evolyx.local'],
                'name' => 'Legs hypertrophie',
                'description' => 'Jambes completes avec quadriceps, ischios et fessiers.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $users['demo@evolyx.local'],
                'name' => 'Upper volume',
                'description' => 'Haut du corps avec plus de volume pour alimenter les courbes de progression.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $users['demo@evolyx.local'],
                'name' => 'Bras epaules',
                'description' => 'Seance courte orientee deltoides, biceps et triceps.',
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

        $sessionExercises = [
            'Push force' => [
                ['Developpe couche halteres', 120],
                ['Developpe incline halteres', 105],
                ['Developpe militaire', 120],
                ['Elevations laterales', 60],
                ['Extension triceps poulie', 60],
            ],
            'Pull dos biceps' => [
                ['Tirage vertical', 120],
                ['Rowing barre', 120],
                ['Rowing poulie basse', 90],
                ['Face pull', 60],
                ['Curl biceps halteres', 60],
            ],
            'Legs hypertrophie' => [
                ['Presse a cuisses', 150],
                ['Squat goblet', 120],
                ['Souleve de terre roumain', 150],
                ['Hip thrust', 120],
                ['Leg curl', 75],
            ],
            'Upper volume' => [
                ['Developpe couche halteres', 90],
                ['Tirage vertical', 90],
                ['Developpe incline halteres', 90],
                ['Rowing poulie basse', 90],
                ['Crunch cable', 60],
            ],
            'Bras epaules' => [
                ['Developpe militaire', 105],
                ['Elevations laterales', 60],
                ['Face pull', 60],
                ['Curl biceps halteres', 60],
                ['Extension triceps poulie', 60],
            ],
        ];

        $pivotRows = [];
        foreach ($sessionExercises as $sessionName => $exerciseRows) {
            foreach ($exerciseRows as $index => [$exerciseName, $restTime]) {
                $pivotRows[] = [
                    'workout_session_id' => $sessions[$sessionName],
                    'exercise_id' => $exercises[$exerciseName],
                    'rest_time' => $restTime,
                    'position' => $index + 1,
                ];
            }
        }

        DB::table('workout_session_exercise')->upsert(
            $pivotRows,
            ['workout_session_id', 'exercise_id'],
            ['rest_time', 'position']
        );

        $completedSchedule = [
            'Push force',
            'Pull dos biceps',
            'Legs hypertrophie',
            'Upper volume',
            'Bras epaules',
        ];

        $performanceProfiles = [
            'Developpe couche halteres' => ['weight' => 18.00, 'step' => 0.65, 'repetitions' => 8, 'rep_cycle' => 4],
            'Developpe incline halteres' => ['weight' => 15.00, 'step' => 0.50, 'repetitions' => 9, 'rep_cycle' => 3],
            'Developpe militaire' => ['weight' => 22.50, 'step' => 0.45, 'repetitions' => 6, 'rep_cycle' => 3],
            'Elevations laterales' => ['weight' => 6.00, 'step' => 0.15, 'repetitions' => 14, 'rep_cycle' => 5],
            'Extension triceps poulie' => ['weight' => 18.00, 'step' => 0.45, 'repetitions' => 12, 'rep_cycle' => 4],
            'Tirage vertical' => ['weight' => 38.00, 'step' => 0.90, 'repetitions' => 10, 'rep_cycle' => 4],
            'Rowing barre' => ['weight' => 35.00, 'step' => 0.80, 'repetitions' => 8, 'rep_cycle' => 4],
            'Rowing poulie basse' => ['weight' => 32.00, 'step' => 0.75, 'repetitions' => 10, 'rep_cycle' => 4],
            'Face pull' => ['weight' => 16.00, 'step' => 0.35, 'repetitions' => 15, 'rep_cycle' => 5],
            'Curl biceps halteres' => ['weight' => 9.00, 'step' => 0.20, 'repetitions' => 11, 'rep_cycle' => 4],
            'Presse a cuisses' => ['weight' => 85.00, 'step' => 2.00, 'repetitions' => 10, 'rep_cycle' => 5],
            'Squat goblet' => ['weight' => 22.00, 'step' => 0.70, 'repetitions' => 12, 'rep_cycle' => 4],
            'Souleve de terre roumain' => ['weight' => 42.50, 'step' => 1.10, 'repetitions' => 8, 'rep_cycle' => 4],
            'Hip thrust' => ['weight' => 60.00, 'step' => 1.60, 'repetitions' => 10, 'rep_cycle' => 5],
            'Leg curl' => ['weight' => 24.00, 'step' => 0.60, 'repetitions' => 12, 'rep_cycle' => 4],
            'Crunch cable' => ['weight' => 20.00, 'step' => 0.50, 'repetitions' => 14, 'rep_cycle' => 4],
        ];

        for ($week = 15; $week >= 0; $week--) {
            foreach ([0, 2, 4] as $dayOffsetIndex => $dayOffset) {
                $sessionName = $completedSchedule[($week + $dayOffsetIndex) % count($completedSchedule)];
                $performedAt = now()
                    ->subWeeks($week)
                    ->startOfWeek()
                    ->addDays($dayOffset)
                    ->setTime(18, 30);

                DB::table('performed_sessions')->updateOrInsert(
                    [
                        'user_id' => $users['demo@evolyx.local'],
                        'workout_session_id' => $sessions[$sessionName],
                        'performed_at' => $performedAt,
                    ],
                    [
                        'completed_at' => $performedAt->copy()->addMinutes(70),
                        'notes' => 'Seance demo validee pour alimenter le suivi de progression.',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $performedSessionId = DB::table('performed_sessions')
                    ->where('user_id', $users['demo@evolyx.local'])
                    ->where('workout_session_id', $sessions[$sessionName])
                    ->where('performed_at', $performedAt)
                    ->value('id');

                $progressIndex = 15 - $week;

                foreach ($sessionExercises[$sessionName] as [$exerciseName]) {
                    $profile = $performanceProfiles[$exerciseName];
                    $weight = round($profile['weight'] + ($progressIndex * $profile['step']) + ($dayOffsetIndex * 0.15), 2);
                    $repetitions = $profile['repetitions'] + (($progressIndex + $dayOffsetIndex) % $profile['rep_cycle']);

                    DB::table('performances')->updateOrInsert(
                        [
                            'performed_session_id' => $performedSessionId,
                            'exercise_id' => $exercises[$exerciseName],
                        ],
                        [
                            'performed_at' => $performedAt,
                            'weight' => $weight,
                            'repetitions' => $repetitions,
                            'duration_minutes' => null,
                            'distance_meters' => null,
                            'user_id' => $users['demo@evolyx.local'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }
        }

        foreach ([
            ['Push force', 1],
            ['Pull dos biceps', 3],
            ['Legs hypertrophie', 5],
            ['Upper volume', 8],
        ] as [$sessionName, $daysFromNow]) {
            $plannedAt = now()->addDays($daysFromNow)->setTime(18, 0);

            DB::table('performed_sessions')->updateOrInsert(
                [
                    'user_id' => $users['demo@evolyx.local'],
                    'workout_session_id' => $sessions[$sessionName],
                    'performed_at' => $plannedAt,
                ],
                [
                    'completed_at' => null,
                    'notes' => 'Seance programmee via les donnees demo.',
                    'created_at' => $now,
                    'updated_at' => $now,
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
