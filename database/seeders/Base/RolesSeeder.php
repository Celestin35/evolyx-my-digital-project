<?php

namespace Database\Seeders\Base;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('roles')->upsert([
            ['name' => 'admin', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'coach', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'user', 'created_at' => $now, 'updated_at' => $now],
        ], ['name'], ['updated_at']);
    }
}
