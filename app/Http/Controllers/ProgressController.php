<?php

namespace App\Http\Controllers;

use App\Models\Performance;
use App\Services\WeightEntriesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ProgressController extends Controller
{
    public function show(Request $request, WeightEntriesService $weightEntriesService): Response
    {
        $activeSubscription = $request->user()
            ->subscriptions()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })
            ->with('subscriptionPlan:id,name,premium_features')
            ->latest('start_date')
            ->first();

        $performances = Performance::query()
            ->where('user_id', $request->user()->id)
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

        return Inertia::render('Progress', [
            'weightEntries' => $weightEntriesService->getForUser($request->user()),
            'sports' => $request->user()
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
            'canViewPerformanceCharts' => (bool) $activeSubscription?->subscriptionPlan?->premium_features,
            'currentSubscriptionPlanName' => $activeSubscription?->subscriptionPlan?->name,
        ]);
    }

    public function storeWeightEntry(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'weight' => ['required', 'numeric', 'min:20', 'max:600'],
            'body_fat' => ['nullable', 'numeric', 'min:2', 'max:75'],
            'entry_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);

        $entryDate = Carbon::createFromFormat('Y-m-d', $validatedData['entry_date'])
            ->startOfDay();

        $weightEntry = $request->user()->weightEntries()->make([
            'weight' => $validatedData['weight'],
            'body_fat' => $validatedData['body_fat'] ?? null,
        ]);
        $weightEntry->created_at = $entryDate;
        $weightEntry->updated_at = now();
        $weightEntry->save();

        return to_route('progress')->with(
            'success',
            'Entrée de poids ajoutée avec succès.',
        );
    }
}
