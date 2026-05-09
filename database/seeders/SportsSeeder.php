<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SportsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $catalog = require database_path('seeders/data/sports_catalog.php');

        foreach ($catalog as $sport) {
            DB::table('sports')->updateOrInsert(
                ['name' => $sport['name']],
                [
                    'display_name' => $sport['display_name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }
}
