<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = DB::table('roles')->pluck('id', 'name');
        DB::table('users')->upsert([
            [
                'email' => 'maya@evolyx.local',
                'email_verified_at' => $now,
                'pseudo' => 'maya_fit',
                'password' => Hash::make('password'),
                'first_name' => 'Maya',
                'sex' => 'female',
                'height' => 164,
                'activity_level' => 'active',
                'birth_date' => '1998-03-12',
                'role_id' => $roles['user'],
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'email' => 'noah@evolyx.local',
                'email_verified_at' => $now,
                'pseudo' => 'noah_run',
                'password' => Hash::make('password'),
                'first_name' => 'Noah',
                'sex' => 'male',
                'height' => 181,
                'activity_level' => 'active',
                'birth_date' => '1994-09-04',
                'role_id' => $roles['user'],
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'email' => 'zoe@evolyx.local',
                'email_verified_at' => $now,
                'pseudo' => 'zoe_lift',
                'password' => Hash::make('password'),
                'first_name' => 'Zoe',
                'sex' => 'female',
                'height' => 170,
                'activity_level' => 'moderate',
                'birth_date' => '1997-11-18',
                'role_id' => $roles['user'],
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ], ['email'], [
            'pseudo',
            'email_verified_at',
            'password',
            'first_name',
            'sex',
            'height',
            'activity_level',
            'birth_date',
            'role_id',
            'updated_at',
            'deleted_at',
        ]);

        $users = DB::table('users')->pluck('id', 'email');
        $plans = DB::table('subscription_plans')->pluck('id', 'name');
        $goalTypes = DB::table('goal_types')->pluck('id', 'name');
        $exercises = DB::table('exercises')->pluck('id', 'name');
        $exerciseAliases = [
            'Développé couché' => ['DÃ©veloppÃ© couchÃ©'],
            'Développé militaire' => ['DÃ©veloppÃ© militaire'],
            'Soulevé de terre' => ['SoulevÃ© de terre'],
            'Presse à cuisses' => ['Presse Ã  cuisses'],
        ];

        foreach ($exerciseAliases as $canonicalName => $aliases) {
            foreach ($aliases as $alias) {
                if (! isset($exercises[$canonicalName]) && isset($exercises[$alias])) {
                    $exercises[$canonicalName] = $exercises[$alias];
                }
            }
        }

        $exerciseId = function (string $name) use ($exercises): ?int {
            return isset($exercises[$name]) ? (int) $exercises[$name] : null;
        };

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

        foreach (['maya@evolyx.local', 'noah@evolyx.local', 'zoe@evolyx.local'] as $friendEmail) {
            DB::table('subscriptions')->updateOrInsert(
                ['user_id' => $users[$friendEmail], 'subscription_plan_id' => $plans['Premium']],
                [
                    'start_date' => now()->subDays(20),
                    'end_date' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        foreach ([
            ['follower_id' => $users['demo@evolyx.local'], 'followed_id' => $users['maya@evolyx.local']],
            ['follower_id' => $users['demo@evolyx.local'], 'followed_id' => $users['noah@evolyx.local']],
            ['follower_id' => $users['maya@evolyx.local'], 'followed_id' => $users['demo@evolyx.local']],
            ['follower_id' => $users['zoe@evolyx.local'], 'followed_id' => $users['demo@evolyx.local']],
        ] as $follow) {
            DB::table('user_follows')->updateOrInsert($follow, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

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
            'sport_id' => $sports['Fitness / musculation'],
        ]);

        foreach (['maya@evolyx.local', 'noah@evolyx.local', 'zoe@evolyx.local'] as $friendEmail) {
            DB::table('sport_user')->updateOrInsert([
                'user_id' => $users[$friendEmail],
                'sport_id' => $sports['Fitness / musculation'],
            ]);
        }

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
                ['Développé couché', 120],
                ['Développé militaire', 120],
                ['Tractions', 90],
                ['Curl biceps', 60],
            ],
            'Pull dos biceps' => [
                ['Tractions', 120],
                ['Soulevé de terre', 150],
                ['Curl biceps', 60],
                ['Gainage', 60],
            ],
            'Legs hypertrophie' => [
                ['Squat', 150],
                ['Presse à cuisses', 120],
                ['Soulevé de terre', 150],
                ['Gainage', 60],
            ],
            'Upper volume' => [
                ['Développé couché', 90],
                ['Tractions', 90],
                ['Développé militaire', 90],
                ['Curl biceps', 60],
            ],
            'Bras epaules' => [
                ['Développé militaire', 105],
                ['Tractions', 90],
                ['Curl biceps', 60],
                ['Gainage', 60],
            ],
        ];

        $pivotRows = [];
        foreach ($sessionExercises as $sessionName => $exerciseRows) {
            foreach ($exerciseRows as $index => [$exerciseName, $restTime]) {
                $resolvedExerciseId = $exerciseId($exerciseName);

                if ($resolvedExerciseId === null) {
                    continue;
                }

                $pivotRows[] = [
                    'workout_session_id' => $sessions[$sessionName],
                    'exercise_id' => $resolvedExerciseId,
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
            'Développé couché' => ['weight' => 42.50, 'step' => 0.80, 'repetitions' => 8, 'rep_cycle' => 4],
            'Développé militaire' => ['weight' => 27.50, 'step' => 0.45, 'repetitions' => 6, 'rep_cycle' => 3],
            'Tractions' => ['weight' => 0.00, 'step' => 0.00, 'repetitions' => 6, 'rep_cycle' => 5],
            'Curl biceps' => ['weight' => 10.00, 'step' => 0.20, 'repetitions' => 11, 'rep_cycle' => 4],
            'Soulevé de terre' => ['weight' => 70.00, 'step' => 1.50, 'repetitions' => 5, 'rep_cycle' => 3],
            'Gainage' => ['weight' => 0.00, 'step' => 0.00, 'repetitions' => 1, 'rep_cycle' => 1],
            'Squat' => ['weight' => 62.50, 'step' => 1.20, 'repetitions' => 8, 'rep_cycle' => 4],
            'Presse à cuisses' => ['weight' => 95.00, 'step' => 2.00, 'repetitions' => 10, 'rep_cycle' => 5],
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
                    $resolvedExerciseId = $exerciseId($exerciseName);

                    if ($resolvedExerciseId === null) {
                        continue;
                    }

                    $profile = $performanceProfiles[$exerciseName];
                    $weight = round($profile['weight'] + ($progressIndex * $profile['step']) + ($dayOffsetIndex * 0.15), 2);
                    $repetitions = $profile['repetitions'] + (($progressIndex + $dayOffsetIndex) % $profile['rep_cycle']);

                    DB::table('performances')->updateOrInsert(
                        [
                            'performed_session_id' => $performedSessionId,
                            'exercise_id' => $resolvedExerciseId,
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

        $communityDemoSessions = [
            [
                'email' => 'maya@evolyx.local',
                'session' => 'Full body controle',
                'description' => 'Travail propre sur les mouvements de base.',
                'performed_at' => now()->subDays(2)->setTime(18, 15),
                'post_title' => 'Full body solide',
                'post_content' => 'Bonnes sensations, surtout sur le squat.',
                'exercises' => [
                    ['Squat', 68.50, 8],
                    ['Tractions', 0.00, 7],
                    ['Curl biceps', 12.00, 10],
                ],
            ],
            [
                'email' => 'noah@evolyx.local',
                'session' => 'Renfo haut du corps',
                'description' => 'Seance courte avant la reprise cardio.',
                'performed_at' => now()->subDays(5)->setTime(12, 30),
                'post_title' => 'Retour propre sur le haut du corps',
                'post_content' => null,
                'exercises' => [
                    ['Tractions', 0.00, 9],
                    ['Gainage', 0.00, 1],
                ],
            ],
        ];

        foreach ($communityDemoSessions as $communitySession) {
            $friendUserId = $users[$communitySession['email']];

            DB::table('workout_sessions')->updateOrInsert(
                ['user_id' => $friendUserId, 'name' => $communitySession['session']],
                [
                    'description' => $communitySession['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $friendWorkoutSessionId = DB::table('workout_sessions')
                ->where('user_id', $friendUserId)
                ->where('name', $communitySession['session'])
                ->value('id');

            DB::table('performed_sessions')->updateOrInsert(
                [
                    'user_id' => $friendUserId,
                    'workout_session_id' => $friendWorkoutSessionId,
                    'performed_at' => $communitySession['performed_at'],
                ],
                [
                    'completed_at' => $communitySession['performed_at']->copy()->addMinutes(55),
                    'notes' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $friendPerformedSessionId = DB::table('performed_sessions')
                ->where('user_id', $friendUserId)
                ->where('workout_session_id', $friendWorkoutSessionId)
                ->where('performed_at', $communitySession['performed_at'])
                ->value('id');

            foreach ($communitySession['exercises'] as $exerciseIndex => [$exerciseName, $weight, $repetitions]) {
                $resolvedExerciseId = $exerciseId($exerciseName);

                if ($resolvedExerciseId === null) {
                    continue;
                }

                DB::table('workout_session_exercise')->updateOrInsert(
                    [
                        'workout_session_id' => $friendWorkoutSessionId,
                        'exercise_id' => $resolvedExerciseId,
                    ],
                    [
                        'rest_time' => 90,
                        'position' => $exerciseIndex + 1,
                    ]
                );

                DB::table('performances')->updateOrInsert(
                    [
                        'performed_session_id' => $friendPerformedSessionId,
                        'exercise_id' => $resolvedExerciseId,
                    ],
                    [
                        'performed_at' => $communitySession['performed_at'],
                        'weight' => $weight,
                        'repetitions' => $repetitions,
                        'duration_minutes' => null,
                        'distance_meters' => null,
                        'user_id' => $friendUserId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            DB::table('community_posts')->updateOrInsert(
                [
                    'user_id' => $friendUserId,
                    'performed_session_id' => $friendPerformedSessionId,
                ],
                [
                    'title' => $communitySession['post_title'],
                    'content' => $communitySession['post_content'],
                    'published_at' => $communitySession['performed_at']->copy()->addMinutes(70),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
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
