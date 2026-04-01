<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SportsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('sports')->upsert([
            ['name' => 'Musculation', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Calisthenie', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Yoga', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pilates', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mobilite', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cardio doux', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Meditation', 'created_at' => $now, 'updated_at' => $now],
        ], ['name'], ['updated_at']);
    }
}
