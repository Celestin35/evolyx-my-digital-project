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
            EquipmentSeeder::class,
            ExerciseCategoriesSeeder::class,
            ExercisesSeeder::class,
            ExerciseEquipmentSeeder::class,
            AdminUserSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
