<?php

namespace Database\Seeders\Sports;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciseMetricsSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = require database_path('seeders/data/sports_catalog.php');
        $sports = DB::table('sports')->pluck('id', 'name');
        $exerciseIds = DB::table('exercises')
            ->whereNull('user_id')
            ->get(['id', 'name', 'sport_id'])
            ->keyBy(fn (object $exercise) => $exercise->sport_id.'|'.$exercise->name);
        $metricIds = DB::table('metrics')->pluck('id', 'key');

        $rows = [];

        foreach ($catalog as $sport) {
            $sportId = $sports[$sport['name']] ?? null;

            if (! $sportId) {
                continue;
            }

            foreach ($sport['activities'] as $activity) {
                $exercise = $exerciseIds[$sportId.'|'.$activity['name']] ?? null;

                if (! $exercise) {
                    continue;
                }

                foreach ($activity['metrics'] as $index => $metricKey) {
                    if (! isset($metricIds[$metricKey])) {
                        continue;
                    }

                    $rows[] = [
                        'exercise_id' => $exercise->id,
                        'metric_id' => $metricIds[$metricKey],
                        'sort_order' => $index + 1,
                    ];
                }
            }
        }

        $systemExerciseIds = $exerciseIds->pluck('id');

        if ($systemExerciseIds->isNotEmpty()) {
            DB::table('exercise_metric')
                ->whereIn('exercise_id', $systemExerciseIds)
                ->delete();
        }

        if ($rows !== []) {
            DB::table('exercise_metric')->insert($rows);
        }
    }
}
