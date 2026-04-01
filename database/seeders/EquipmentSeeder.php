<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('equipment')->upsert([
            ['name' => 'Poids du corps', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Halteres', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Barre olympique', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Banc', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kettlebell', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bandes elastiques', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Tapis', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Barre de traction', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Anneaux de gymnastique', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Foam roller', 'created_at' => $now, 'updated_at' => $now],
        ], ['name'], ['updated_at']);
    }
}
