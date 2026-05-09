<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $catalog = require database_path('seeders/data/sports_catalog.php');
        $sports = DB::table('sports')->pluck('id', 'name');
        $categories = DB::table('exercise_categories')->pluck('id', 'name');
        $catalogExerciseNames = collect($catalog)
            ->flatMap(fn (array $sport) => collect($sport['activities'])->pluck('name'))
            ->values();

        DB::table('exercises')
            ->whereNull('user_id')
            ->whereNotIn('name', $catalogExerciseNames)
            ->delete();

        foreach ($catalog as $sport) {
            foreach ($sport['activities'] as $activity) {
                DB::table('exercises')->updateOrInsert(
                    ['name' => $activity['name'], 'user_id' => null],
                    [
                        'description' => $activity['description'],
                        'exercise_category_id' => $categories[$activity['category']],
                        'sport_id' => $sports[$sport['name']],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }
        }
    }
}
