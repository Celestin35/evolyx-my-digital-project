<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            GoalTypesSeeder::class,
            SubscriptionPlansSeeder::class,
            SportsSeeder::class,
            ExerciseCategoriesSeeder::class,
            EquipmentSeeder::class,
            MetricsSeeder::class,
            ExercisesSeeder::class,
            ExerciseEquipmentSeeder::class,
            ExerciseMetricsSeeder::class,
            WorkoutSessionsSeeder::class,
            AdminUserSeeder::class,
            DemoDataSeeder::class,
            WeightEntriesSeeder::class,
        ]);
    }
}
