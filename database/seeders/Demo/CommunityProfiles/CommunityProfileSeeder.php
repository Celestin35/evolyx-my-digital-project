<?php

namespace Database\Seeders\Demo\CommunityProfiles;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

abstract class CommunityProfileSeeder extends Seeder
{
    abstract protected function profile(): array;

    abstract protected function sharedSessions(): array;

    public function run(): void
    {
        $now = now();
        $profile = $this->profile();
        $roles = DB::table('roles')->pluck('id', 'name');
        $plans = DB::table('subscription_plans')->pluck('id', 'name');
        $sports = DB::table('sports')->pluck('id', 'name');

        DB::table('users')->upsert([[
            'email' => $profile['email'],
            'email_verified_at' => $now,
            'pseudo' => $profile['pseudo'],
            'password' => Hash::make('password'),
            'first_name' => $profile['first_name'],
            'sex' => $profile['sex'],
            'height' => $profile['height'],
            'activity_level' => $profile['activity_level'],
            'birth_date' => $profile['birth_date'],
            'role_id' => $roles['user'],
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]], ['email'], [
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

        $userId = DB::table('users')->where('email', $profile['email'])->value('id');
        $demoUserId = DB::table('users')->where('email', 'demo@evolyx.local')->value('id');

        DB::table('subscriptions')->updateOrInsert(
            ['user_id' => $userId, 'subscription_plan_id' => $plans['Premium']],
            [
                'start_date' => $now->copy()->subDays(20),
                'end_date' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        foreach ($profile['sports'] as $sportName) {
            $sportId = $sports[$sportName] ?? null;

            if ($sportId) {
                DB::table('sport_user')->updateOrInsert([
                    'user_id' => $userId,
                    'sport_id' => $sportId,
                ]);
            }
        }

        if ($demoUserId) {
            foreach ([
                ['follower_id' => $demoUserId, 'followed_id' => $userId],
                ['follower_id' => $userId, 'followed_id' => $demoUserId],
            ] as $follow) {
                DB::table('user_follows')->updateOrInsert($follow, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->clearExistingSharedData($userId);

        foreach ($this->sharedSessions() as $sharedSession) {
            $this->seedSharedSession($userId, $sharedSession, $now);
        }
    }

    private function clearExistingSharedData(int $userId): void
    {
        DB::table('community_posts')->where('user_id', $userId)->delete();
        DB::table('performances')->where('user_id', $userId)->delete();
        DB::table('performed_sessions')->where('user_id', $userId)->delete();
        DB::table('workout_sessions')->where('user_id', $userId)->delete();
    }

    private function seedSharedSession(int $userId, array $sharedSession, $now): void
    {
        $exerciseIds = DB::table('exercises')->pluck('id', 'name');

        DB::table('workout_sessions')->updateOrInsert(
            ['user_id' => $userId, 'name' => $sharedSession['session']],
            [
                'description' => $sharedSession['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        $workoutSessionId = DB::table('workout_sessions')
            ->where('user_id', $userId)
            ->where('name', $sharedSession['session'])
            ->value('id');

        foreach ($sharedSession['performances'] as $position => $performance) {
            $exerciseId = $exerciseIds[$performance['exercise']] ?? null;

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

        $performedAt = now()
            ->startOfMonth()
            ->addDays($sharedSession['day'] - 1)
            ->setTime($sharedSession['hour'], $sharedSession['minute'] ?? 0);

        DB::table('performed_sessions')->updateOrInsert(
            [
                'user_id' => $userId,
                'workout_session_id' => $workoutSessionId,
                'performed_at' => $performedAt,
            ],
            [
                'completed_at' => $performedAt->copy()->addMinutes($sharedSession['duration_minutes'] ?? 55),
                'notes' => $sharedSession['notes'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        $performedSessionId = DB::table('performed_sessions')
            ->where('user_id', $userId)
            ->where('workout_session_id', $workoutSessionId)
            ->where('performed_at', $performedAt)
            ->value('id');

        foreach ($sharedSession['performances'] as $performance) {
            $exerciseId = $exerciseIds[$performance['exercise']] ?? null;

            if (! $exerciseId) {
                continue;
            }

            DB::table('performances')->updateOrInsert(
                [
                    'performed_session_id' => $performedSessionId,
                    'exercise_id' => $exerciseId,
                ],
                [
                    'performed_at' => $performedAt,
                    'weight' => $performance['weight'] ?? null,
                    'repetitions' => $performance['repetitions'] ?? null,
                    'duration_minutes' => $performance['duration_minutes'] ?? null,
                    'distance_meters' => $performance['distance_meters'] ?? null,
                    'user_id' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        DB::table('community_posts')->updateOrInsert(
            [
                'user_id' => $userId,
                'performed_session_id' => $performedSessionId,
            ],
            [
                'title' => $sharedSession['post_title'],
                'content' => $sharedSession['post_content'] ?? null,
                'published_at' => $performedAt->copy()->addMinutes(($sharedSession['duration_minutes'] ?? 55) + 10),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
    }
}
