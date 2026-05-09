<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciseMetricsSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = require database_path('seeders/data/sports_catalog.php');
        $exerciseIds = DB::table('exercises')->pluck('id', 'name');
        $metricIds = DB::table('metrics')->pluck('id', 'key');

        $rows = [];

        foreach ($catalog as $sport) {
            foreach ($sport['activities'] as $activity) {
                if (! isset($exerciseIds[$activity['name']])) {
                    continue;
                }

                foreach ($activity['metrics'] as $index => $metricKey) {
                    if (! isset($metricIds[$metricKey])) {
                        continue;
                    }

                    $rows[] = [
                        'exercise_id' => $exerciseIds[$activity['name']],
                        'metric_id' => $metricIds[$metricKey],
                        'is_required' => in_array($metricKey, $activity['required_metrics'], true),
                        'is_primary' => $metricKey === $activity['primary_progress_metric'],
                        'sort_order' => $index + 1,
                    ];
                }
            }
        }

        DB::table('exercise_metric')->upsert(
            $rows,
            ['exercise_id', 'metric_id'],
            ['is_required', 'is_primary', 'sort_order'],
        );
    }
}
