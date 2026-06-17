<?php

namespace Database\Seeders;

use Database\Seeders\Base\GoalTypesSeeder;
use Database\Seeders\Base\MetricsSeeder;
use Database\Seeders\Base\RolesSeeder;
use Database\Seeders\Base\SubscriptionPlansSeeder;
use Database\Seeders\Demo\AdminUserSeeder;
use Database\Seeders\Demo\DemoDataSeeder;
use Database\Seeders\Sports\ExerciseCategoriesSeeder;
use Database\Seeders\Sports\ExerciseMetricsSeeder;
use Database\Seeders\Sports\ExercisesSeeder;
use Database\Seeders\Sports\SportsSeeder;
use Database\Seeders\Sports\WorkoutSessionsSeeder;
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
            MetricsSeeder::class,
            ExercisesSeeder::class,
            ExerciseMetricsSeeder::class,
            WorkoutSessionsSeeder::class,
            AdminUserSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
