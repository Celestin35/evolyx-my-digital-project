<?php

namespace Database\Seeders\Demo;

use Database\Seeders\Demo\CommunityProfiles\MayaProfileSeeder;
use Database\Seeders\Demo\CommunityProfiles\NoahProfileSeeder;
use Database\Seeders\Demo\CommunityProfiles\ZoeProfileSeeder;
use Database\Seeders\Demo\MainUser\DemoProfileSeeder;
use Database\Seeders\Demo\MainUser\DemoWorkoutSessionsSeeder;
use Database\Seeders\Demo\MainUser\WeightEntriesSeeder;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoProfileSeeder::class,
            DemoWorkoutSessionsSeeder::class,
            WeightEntriesSeeder::class,
            MayaProfileSeeder::class,
            NoahProfileSeeder::class,
            ZoeProfileSeeder::class,
        ]);
    }
}
