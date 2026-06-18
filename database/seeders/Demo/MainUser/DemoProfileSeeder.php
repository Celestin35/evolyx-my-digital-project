<?php

namespace Database\Seeders\Demo\MainUser;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoProfileSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $roles = DB::table('roles')->pluck('id', 'name');
        $plans = DB::table('subscription_plans')->pluck('id', 'name');
        $sports = DB::table('sports')->pluck('id', 'name');
        $goalTypes = DB::table('goal_types')->pluck('id', 'name');

        DB::table('users')->upsert([
            [
                'email' => 'demo@evolyx.local',
                'email_verified_at' => $now,
                'pseudo' => 'lina',
                'password' => Hash::make('password'),
                'first_name' => 'Lina',
                'sex' => 'female',
                'height' => 168,
                'activity_level' => 'moderate',
                'birth_date' => '1996-05-22',
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

        $userId = DB::table('users')->where('email', 'demo@evolyx.local')->value('id');

        DB::table('subscriptions')->updateOrInsert(
            ['user_id' => $userId, 'subscription_plan_id' => $plans['Premium']],
            [
                'start_date' => $now->copy()->subDays(30),
                'end_date' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        foreach (['Fitness / musculation', 'Course à pied / running'] as $sportName) {
            $sportId = $sports[$sportName] ?? null;

            if ($sportId) {
                DB::table('sport_user')->updateOrInsert([
                    'user_id' => $userId,
                    'sport_id' => $sportId,
                ]);
            }
        }

        DB::table('macronutrients')->updateOrInsert(
            ['protein' => 135, 'carbs' => 245, 'fats' => 68],
            ['created_at' => $now, 'updated_at' => $now],
        );

        $macroId = DB::table('macronutrients')
            ->where(['protein' => 135, 'carbs' => 245, 'fats' => 68])
            ->value('id');

        DB::table('goals')->updateOrInsert(
            ['user_id' => $userId, 'is_active' => true],
            [
                'target_weight' => 64.50,
                'weekly_weight_goal' => -0.25,
                'daily_calories' => 2132,
                'goal_end_date' => $now->copy()->addWeeks(8)->toDateString(),
                'macronutrient_id' => $macroId,
                'goal_type_id' => $goalTypes['Perte de poids'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
    }
}
