<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciseCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $catalog = require database_path('seeders/data/sports_catalog.php');

        $categories = collect($catalog)
            ->flatMap(fn (array $sport) => collect($sport['activities'])->pluck('category'))
            ->unique()
            ->sort()
            ->values();

        foreach ($categories as $category) {
            DB::table('exercise_categories')->updateOrInsert(
                ['name' => $category],
                ['created_at' => $now, 'updated_at' => $now],
            );
        }
    }
}
