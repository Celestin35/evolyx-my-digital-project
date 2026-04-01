<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciseCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('exercise_categories')->upsert([
            ['name' => 'Push', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pull', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Legs', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Core', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mobilite', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cardio', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Respiration', 'created_at' => $now, 'updated_at' => $now],
        ], ['name'], ['updated_at']);
    }
}
