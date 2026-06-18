<?php

namespace Database\Seeders\Sports;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $catalog = require database_path('seeders/data/sports_catalog.php');
        $categoryNames = require database_path('seeders/data/exercise_categories.php');
        $sports = DB::table('sports')->pluck('id', 'name');
        $categories = DB::table('exercise_categories')->pluck('id', 'name');
        $catalogExerciseKeys = collect($catalog)
            ->flatMap(fn (array $sport) => collect($sport['activities'])
                ->map(fn (array $activity) => ($sports[$sport['name']] ?? 'missing').'|'.$activity['name']))
            ->flip();

        $staleExerciseIds = DB::table('exercises')
            ->whereNull('user_id')
            ->get(['id', 'name', 'sport_id'])
            ->reject(fn (object $exercise) => $catalogExerciseKeys->has($exercise->sport_id.'|'.$exercise->name))
            ->pluck('id');

        if ($staleExerciseIds->isNotEmpty()) {
            DB::table('exercises')
                ->whereIn('id', $staleExerciseIds)
                ->delete();
        }

        foreach ($catalog as $sport) {
            $sportId = $sports[$sport['name']] ?? null;

            if (! $sportId) {
                continue;
            }

            foreach ($sport['activities'] as $activity) {
                $categoryName = $categoryNames[$activity['category']] ?? $activity['category'];

                DB::table('exercises')->updateOrInsert(
                    ['name' => $activity['name'], 'sport_id' => $sportId, 'user_id' => null],
                    [
                        'description' => $activity['description'],
                        'exercise_category_id' => $categories[$categoryName],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }
        }
    }
}
