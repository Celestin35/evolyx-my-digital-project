<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GoalTypesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('goal_types')->upsert([
            ['name' => 'Perte de poids', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Maintien', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Prise de masse', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Recomposition corporelle', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bien-etre', 'created_at' => $now, 'updated_at' => $now],
        ], ['name'], ['updated_at']);
    }
}
