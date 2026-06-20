<?php

namespace App\Services;

use App\Models\Performance;
use App\Models\User;
use Illuminate\Support\Carbon;

class ProgressDataService
{
    public function __construct(
        private readonly WeightEntriesService $weightEntriesService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function getForUser(User $user): array
    {
        $performances = Performance::query()
            ->where('user_id', $user->id)
            ->with([
                'exercise:id,name,sport_id',
                'exercise.sport:id,name',
                'exercise.metrics:id,key,label,unit,value_type',
                'metricValues.metric:id,key,label,unit,value_type',
            ])
            ->latest('performed_at')
            ->get([
                'id',
                'performed_at',
                'weight',
                'repetitions',
                'duration_minutes',
                'distance_meters',
                'exercise_id',
            ]);

        return [
            'weightEntries' => $this->weightEntriesService->getForUser($user),
            'sports' => $user
                ->sports()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['sports.id', 'sports.name'])
                ->map(fn ($sport) => [
                    'id' => $sport->id,
                    'name' => $sport->name,
                ]),
            'performances' => $performances->map(fn ($performance) => [
                'id' => $performance->id,
                'performed_at' => $performance->performed_at?->toISOString(),
                'weight' => $performance->weight !== null ? (float) $performance->weight : null,
                'repetitions' => $performance->repetitions,
                'duration_minutes' => $performance->duration_minutes !== null
                    ? (float) $performance->duration_minutes
                    : null,
                'distance_meters' => $performance->distance_meters !== null
                    ? (float) $performance->distance_meters
                    : null,
                'exercise_id' => $performance->exercise_id,
                'exercise_name' => $performance->exercise?->name ?? 'Exercice',
                'sport_id' => $performance->exercise?->sport_id,
                'sport_name' => $performance->exercise?->sport?->name,
                'available_metrics' => $performance->exercise?->metrics
                    ->map(fn ($metric) => [
                        'key' => $metric->key,
                        'label' => $metric->label,
                        'unit' => $metric->unit,
                        'value_type' => $metric->value_type,
                        'sort_order' => $metric->pivot->sort_order,
                    ])
                    ->values() ?? [],
                'metric_values' => $performance->metricValues->mapWithKeys(fn ($metricValue) => [
                    $metricValue->metric?->key => (float) $metricValue->value,
                ]),
            ]),
            'canViewPerformanceCharts' => $this->subscriptionService->canViewPerformanceCharts($user),
            'currentSubscriptionPlanName' => $this->subscriptionService->activePlanName($user),
        ];
    }

    public function storeWeightEntry(User $user, array $data): void
    {
        $entryDate = Carbon::createFromFormat('Y-m-d', $data['entry_date'])
            ->startOfDay();

        $weightEntry = $user->weightEntries()->make([
            'weight' => $data['weight'],
            'body_fat' => $data['body_fat'] ?? null,
        ]);
        $weightEntry->created_at = $entryDate;
        $weightEntry->updated_at = now();
        $weightEntry->save();
    }
}
