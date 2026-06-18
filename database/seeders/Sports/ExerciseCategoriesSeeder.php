<?php

namespace Database\Seeders\Sports;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciseCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $categories = require database_path('seeders/data/exercise_categories.php');

        foreach ($categories as $categoryName) {
            DB::table('exercise_categories')->updateOrInsert(
                ['name' => $categoryName],
                ['created_at' => $now, 'updated_at' => $now],
            );
        }
    }
}
