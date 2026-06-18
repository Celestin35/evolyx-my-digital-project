<?php

namespace Database\Seeders\Demo\MainUser;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoWorkoutSessionsSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->where('email', 'demo@evolyx.local')->value('id');

        if (! $userId) {
            return;
        }

        $this->clearExistingWorkoutData($userId);
    }

    private function clearExistingWorkoutData(int $userId): void
    {
        DB::table('community_posts')->where('user_id', $userId)->delete();
        DB::table('performances')->where('user_id', $userId)->delete();
        DB::table('performed_sessions')->where('user_id', $userId)->delete();
        DB::table('workout_sessions')->where('user_id', $userId)->delete();
    }
}
