<?php

namespace App\Services;

use App\Models\Metric;
use App\Models\Performance;
use App\Models\PerformedSession;
use App\Models\User;
use Illuminate\Support\Collection;

class PerformanceRecordingService
{
    public function record(User $user, PerformedSession $performedSession, Collection $performances): void
    {
        $metricIdsByKey = Metric::query()->pluck('id', 'key');

        foreach ($performances as $performanceData) {
            $metrics = collect($performanceData['metrics'] ?? []);
            $performance = Performance::query()->updateOrCreate(
                [
                    'performed_session_id' => $performedSession->id,
                    'exercise_id' => $performanceData['exercise_id'],
                ],
                [
                    'user_id' => $user->id,
                    'performed_at' => $performedSession->performed_at,
                    'weight' => $metrics->get('weight_kg', $performanceData['weight'] ?? null),
                    'repetitions' => $metrics->get('repetitions', $performanceData['repetitions'] ?? null),
                    'duration_minutes' => $metrics->get('duration_minutes', $performanceData['duration_minutes'] ?? null),
                    'distance_meters' => $metrics->get('distance_meters', $performanceData['distance_meters'] ?? null),
                ],
            );

            $performance->metricValues()->delete();

            foreach ($metrics as $metricKey => $value) {
                if ($value === null || $value === '' || ! isset($metricIdsByKey[$metricKey])) {
                    continue;
                }

                $performance->metricValues()->create([
                    'metric_id' => $metricIdsByKey[$metricKey],
                    'value' => $value,
                ]);
            }
        }
    }
}
