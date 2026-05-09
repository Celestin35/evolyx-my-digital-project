<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\Metric;
use App\Models\PerformedSession;
use App\Models\Performance;
use App\Models\Sport;
use App\Models\WorkoutSession;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SessionsController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $userSportIds = $user->sports()->pluck('sports.id');

        $sports = Sport::query()
            ->whereIn('id', $userSportIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $exerciseCategories = ExerciseCategory::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $availableExercises = Exercise::query()
            ->where(function ($query) use ($user) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            })
            ->whereIn('sport_id', $userSportIds)
            ->with(['sport:id,name', 'category:id,name', 'metrics:id,key,label,unit,value_type'])
            ->orderBy('sport_id')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'sport_id', 'exercise_category_id', 'user_id']);

        $workoutSessions = WorkoutSession::query()
            ->where(function ($query) use ($user, $userSportIds) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($systemQuery) use ($userSportIds) {
                        $systemQuery->whereNull('user_id')
                            ->whereHas('exercises', fn ($exerciseQuery) => $exerciseQuery
                                ->whereIn('exercises.sport_id', $userSportIds));
                    });
            })
            ->with([
                'exercises' => fn ($query) => $query
                    ->select(['exercises.id', 'exercises.name', 'sport_id', 'exercise_category_id'])
                    ->with(['sport:id,name', 'metrics:id,key,label,unit,value_type'])
                    ->orderBy('workout_session_exercise.position'),
            ])
            ->latest()
            ->get(['id', 'name', 'description', 'user_id', 'created_at']);

        $performedSessions = PerformedSession::query()
            ->where('user_id', $user->id)
            ->with([
                'workoutSession:id,name',
                'performances:id,performed_session_id,exercise_id,weight,repetitions,duration_minutes,distance_meters',
                'performances.metricValues.metric:id,key,label,unit,value_type',
            ])
            ->orderBy('performed_at')
            ->get(['id', 'user_id', 'workout_session_id', 'performed_at', 'completed_at', 'notes']);

        return Inertia::render('Sessions', [
            'sports' => $sports->map(fn ($sport) => [
                'id' => $sport->id,
                'name' => $sport->name,
            ]),
            'exerciseCategories' => $exerciseCategories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
            ]),
            'availableExercises' => $availableExercises->map(fn ($exercise) => [
                'id' => $exercise->id,
                'name' => $exercise->name,
                'description' => $exercise->description,
                'sport_id' => $exercise->sport_id,
                'sport_name' => $exercise->sport?->name,
                'category_name' => $exercise->category?->name,
                'is_custom' => $exercise->user_id !== null,
                'metrics' => $this->formatExerciseMetrics($exercise),
            ]),
            'workoutSessions' => $workoutSessions->map(fn ($session) => [
                'id' => $session->id,
                'name' => $session->name,
                'description' => $session->description,
                'created_at' => $session->created_at?->toISOString(),
                'is_system' => $session->user_id === null,
                'exercises' => $session->exercises->map(fn ($exercise) => [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'sport_id' => $exercise->sport_id,
                    'sport_name' => $exercise->sport?->name,
                    'metrics' => $this->formatExerciseMetrics($exercise),
                ])->values(),
            ]),
            'performedSessions' => $performedSessions->map(fn ($performedSession) => [
                'id' => $performedSession->id,
                'workout_session_id' => $performedSession->workout_session_id,
                'workout_session_name' => $performedSession->workoutSession?->name,
                'performed_at' => $performedSession->performed_at?->toISOString(),
                'completed_at' => $performedSession->completed_at?->toISOString(),
                'notes' => $performedSession->notes,
                'performances' => $performedSession->performances->map(fn ($performance) => [
                    'exercise_id' => $performance->exercise_id,
                    'weight' => $performance->weight !== null ? (float) $performance->weight : null,
                    'repetitions' => $performance->repetitions,
                    'duration_minutes' => $performance->duration_minutes !== null
                        ? (float) $performance->duration_minutes
                        : null,
                    'distance_meters' => $performance->distance_meters !== null
                        ? (float) $performance->distance_meters
                        : null,
                    'metric_values' => $performance->metricValues->mapWithKeys(fn ($metricValue) => [
                        $metricValue->metric?->key => (float) $metricValue->value,
                    ]),
                ])->values(),
            ]),
        ]);
    }

    public function storeWorkoutSession(Request $request): RedirectResponse
    {
        $user = $request->user();
        $userSportIds = $user->sports()->pluck('sports.id');

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'exercise_ids' => ['required', 'array', 'min:1'],
            'exercise_ids.*' => [
                'integer',
                Rule::exists('exercises', 'id')->where(function ($query) use ($user) {
                    $query->whereNull('user_id')
                        ->orWhere('user_id', $user->id);
                }),
            ],
        ]);

        $belongsToUserSports = Exercise::query()
            ->whereIn('id', $validatedData['exercise_ids'])
            ->whereIn('sport_id', $userSportIds)
            ->count() === count(array_unique($validatedData['exercise_ids']));

        if (! $belongsToUserSports) {
            return back()->withErrors([
                'exercise_ids' => 'Certains exercices ne correspondent pas a vos sports.',
            ]);
        }

        DB::transaction(function () use ($user, $validatedData) {
            $workoutSession = WorkoutSession::query()->create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? null,
                'user_id' => $user->id,
            ]);

            $exercisePivotData = [];
            foreach (array_values(array_unique($validatedData['exercise_ids'])) as $index => $exerciseId) {
                $exercisePivotData[$exerciseId] = [
                    'position' => $index + 1,
                    'rest_time' => null,
                ];
            }

            $workoutSession->exercises()->attach($exercisePivotData);
        });

        return to_route('sessions')->with(
            'success',
            'Séance type créée avec succès.',
        );
    }

    public function updateWorkoutSession(Request $request, WorkoutSession $workoutSession): RedirectResponse
    {
        if ($workoutSession->user_id !== $request->user()->id) {
            throw new AuthorizationException();
        }

        $userSportIds = $request->user()->sports()->pluck('sports.id');

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'exercise_ids' => ['required', 'array', 'min:1'],
            'exercise_ids.*' => [
                'integer',
                Rule::exists('exercises', 'id')->where(function ($query) use ($request) {
                    $query->whereNull('user_id')
                        ->orWhere('user_id', $request->user()->id);
                }),
            ],
        ]);

        $belongsToUserSports = Exercise::query()
            ->whereIn('id', $validatedData['exercise_ids'])
            ->whereIn('sport_id', $userSportIds)
            ->count() === count(array_unique($validatedData['exercise_ids']));

        if (! $belongsToUserSports) {
            return back()->withErrors([
                'exercise_ids' => 'Certains exercices ne correspondent pas à vos sports.',
            ]);
        }

        DB::transaction(function () use ($workoutSession, $validatedData) {
            $workoutSession->update([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? null,
            ]);

            $exercisePivotData = [];
            foreach (array_values(array_unique($validatedData['exercise_ids'])) as $index => $exerciseId) {
                $exercisePivotData[$exerciseId] = [
                    'position' => $index + 1,
                    'rest_time' => null,
                ];
            }

            $workoutSession->exercises()->sync($exercisePivotData);
        });

        return to_route('sessions')->with(
            'success',
            'Séance type mise à jour avec succès.',
        );
    }

    public function destroyWorkoutSession(Request $request, WorkoutSession $workoutSession): RedirectResponse
    {
        if ($workoutSession->user_id !== $request->user()->id) {
            throw new AuthorizationException();
        }

        if ($workoutSession->performedSessions()->exists()) {
            return back()->withErrors([
                'workout_session' => 'Cette séance type est déjà utilisée dans le calendrier.',
            ]);
        }

        DB::transaction(function () use ($workoutSession) {
            $workoutSession->exercises()->detach();
            $workoutSession->delete();
        });

        return to_route('sessions')->with(
            'success',
            'Séance type supprimée avec succès.',
        );
    }

    public function storeExercise(Request $request): RedirectResponse
    {
        $userSportIds = $request->user()->sports()->pluck('sports.id');

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sport_id' => ['required', 'integer', Rule::exists('sports', 'id')->where(
                fn ($query) => $query->whereIn('id', $userSportIds),
            )],
            'exercise_category_id' => ['required', 'integer', Rule::exists('exercise_categories', 'id')],
        ]);

        $exercise = Exercise::query()->create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'sport_id' => $validatedData['sport_id'],
            'exercise_category_id' => $validatedData['exercise_category_id'],
            'user_id' => $request->user()->id,
        ]);

        $this->attachDefaultMetrics($exercise);

        return to_route('sessions')->with(
            'success',
            'Exercice personnalisé créé avec succès.',
        );
    }

    public function updateExercise(Request $request, Exercise $exercise): RedirectResponse
    {
        if ($exercise->user_id !== $request->user()->id) {
            throw new AuthorizationException();
        }

        $userSportIds = $request->user()->sports()->pluck('sports.id');

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sport_id' => ['required', 'integer', Rule::exists('sports', 'id')->where(
                fn ($query) => $query->whereIn('id', $userSportIds),
            )],
            'exercise_category_id' => ['required', 'integer', Rule::exists('exercise_categories', 'id')],
        ]);

        $exercise->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'sport_id' => $validatedData['sport_id'],
            'exercise_category_id' => $validatedData['exercise_category_id'],
        ]);

        $this->attachDefaultMetrics($exercise);

        return to_route('sessions')->with(
            'success',
            'Exercice personnalisé mis à jour avec succès.',
        );
    }

    public function destroyExercise(Request $request, Exercise $exercise): RedirectResponse
    {
        if ($exercise->user_id !== $request->user()->id) {
            throw new AuthorizationException();
        }

        if ($exercise->performances()->exists() || $exercise->workoutSessions()->exists()) {
            return back()->withErrors([
                'exercise' => 'Cet exercice est déjà utilisé dans une séance.',
            ]);
        }

        DB::transaction(function () use ($exercise) {
            $exercise->metrics()->detach();
            $exercise->equipment()->detach();
            $exercise->delete();
        });

        return to_route('sessions')->with(
            'success',
            'Exercice personnalisé supprimé avec succès.',
        );
    }

    public function storePerformedSession(Request $request): RedirectResponse
    {
        $userSportIds = $request->user()->sports()->pluck('sports.id');

        $validatedData = $request->validate([
            'workout_session_id' => [
                'required',
                'integer',
                Rule::exists('workout_sessions', 'id')->where(
                    fn ($query) => $query
                        ->where('user_id', $request->user()->id)
                        ->orWhereNull('user_id'),
                ),
            ],
            'performed_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $workoutSession = WorkoutSession::query()
            ->with('exercises:id,sport_id')
            ->findOrFail($validatedData['workout_session_id']);

        if (
            $workoutSession->user_id !== $request->user()->id &&
            $workoutSession->user_id !== null
        ) {
            throw new AuthorizationException();
        }

        if (
            $workoutSession->user_id === null &&
            $workoutSession->exercises
                ->pluck('sport_id')
                ->diff($userSportIds)
                ->isNotEmpty()
        ) {
            return back()->withErrors([
                'workout_session_id' => 'Cette séance type ne correspond pas à vos sports.',
            ]);
        }

        PerformedSession::query()->create([
            'user_id' => $request->user()->id,
            'workout_session_id' => $validatedData['workout_session_id'],
            'performed_at' => $validatedData['performed_at'],
            'notes' => $validatedData['notes'] ?? null,
        ]);

        return to_route('sessions')->with(
            'success',
            'Séance ajoutée au calendrier.',
        );
    }

    public function completePerformedSession(Request $request, PerformedSession $performedSession): RedirectResponse
    {
        if ($performedSession->user_id !== $request->user()->id) {
            throw new AuthorizationException();
        }

        if ($performedSession->completed_at !== null) {
            return back()->withErrors([
                'performed_session_id' => 'Cette séance est déjà validée.',
            ]);
        }

        if ($performedSession->performed_at->copy()->startOfDay()->isAfter(today())) {
            return back()->withErrors([
                'performed_session_id' => 'Vous pouvez valider uniquement une seance prevue aujourd hui ou avant.',
            ]);
        }

        $validatedData = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'performances' => ['nullable', 'array'],
            'performances.*.exercise_id' => ['required', 'integer'],
            'performances.*.weight' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'performances.*.repetitions' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'performances.*.duration_minutes' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'performances.*.distance_meters' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'performances.*.metrics' => ['nullable', 'array'],
            'performances.*.metrics.*' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        $workoutExerciseIds = $performedSession->workoutSession()
            ->firstOrFail()
            ->exercises()
            ->pluck('exercises.id')
            ->all();

        $submittedPerformances = collect($validatedData['performances'] ?? [])
            ->filter(function (array $performance) {
                return collect([
                    ...array_values($performance['metrics'] ?? []),
                    $performance['weight'] ?? null,
                    $performance['repetitions'] ?? null,
                    $performance['duration_minutes'] ?? null,
                    $performance['distance_meters'] ?? null,
                ])->contains(fn ($value) => $value !== null && $value !== '');
            })
            ->values();

        $invalidExercise = $submittedPerformances
            ->pluck('exercise_id')
            ->diff($workoutExerciseIds)
            ->isNotEmpty();

        if ($invalidExercise) {
            return back()->withErrors([
                'performances' => 'Certains exercices ne font pas partie de cette seance.',
            ]);
        }

        $exerciseMetrics = Exercise::query()
            ->whereIn('id', $workoutExerciseIds)
            ->with('metrics:id,key')
            ->get(['id'])
            ->keyBy('id')
            ->map(fn ($exercise) => $exercise->metrics);

        foreach ($submittedPerformances as $performanceData) {
            $metrics = $exerciseMetrics[$performanceData['exercise_id']] ?? collect();
            $metricKeys = $metrics->pluck('key');
            $submittedMetricKeys = collect($performanceData['metrics'] ?? [])->keys();

            if ($metrics->isNotEmpty() && $submittedMetricKeys->diff($metricKeys)->isNotEmpty()) {
                return back()->withErrors([
                    'performances' => 'Certaines metriques ne correspondent pas a cet exercice.',
                ]);
            }

            $missingRequiredMetric = $metrics
                ->filter(fn ($metric) => (bool) $metric->pivot->is_required)
                ->contains(function ($metric) use ($performanceData) {
                    $value = $performanceData['metrics'][$metric->key] ?? null;

                    return $value === null || $value === '';
                });

            if ($missingRequiredMetric) {
                return back()->withErrors([
                    'performances' => 'Certaines metriques obligatoires sont manquantes.',
                ]);
            }
        }

        $metricIdsByKey = Metric::query()->pluck('id', 'key');

        DB::transaction(function () use ($request, $performedSession, $validatedData, $submittedPerformances, $metricIdsByKey) {
            $performedSession->update([
                'completed_at' => now(),
                'notes' => $validatedData['notes'] ?? $performedSession->notes,
            ]);

            foreach ($submittedPerformances as $performanceData) {
                $metrics = collect($performanceData['metrics'] ?? []);
                $performance = Performance::query()->updateOrCreate(
                    [
                        'performed_session_id' => $performedSession->id,
                        'exercise_id' => $performanceData['exercise_id'],
                    ],
                    [
                        'user_id' => $request->user()->id,
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
        });

        return to_route('sessions')->with(
            'success',
            'Séance validée avec succès.',
        );
    }

    private function formatExerciseMetrics(Exercise $exercise): array
    {
        return $exercise->metrics
            ->map(fn ($metric) => [
                'key' => $metric->key,
                'label' => $metric->label,
                'unit' => $metric->unit,
                'value_type' => $metric->value_type,
                'is_required' => (bool) $metric->pivot->is_required,
                'is_primary' => (bool) $metric->pivot->is_primary,
                'sort_order' => $metric->pivot->sort_order,
            ])
            ->values()
            ->all();
    }

    private function attachDefaultMetrics(Exercise $exercise): void
    {
        $category = ExerciseCategory::query()
            ->whereKey($exercise->exercise_category_id)
            ->value('name');

        $metricKeys = match ($category) {
            'Force' => ['sets', 'repetitions', 'weight_kg', 'perceived_effort'],
            'Core' => ['sets', 'repetitions', 'duration_minutes', 'perceived_effort'],
            'Endurance', 'Vitesse', 'Puissance' => ['duration_minutes', 'distance_meters', 'perceived_effort'],
            default => ['duration_minutes', 'perceived_effort'],
        };

        $requiredMetricKeys = match ($category) {
            'Force' => ['sets', 'repetitions'],
            default => ['duration_minutes'],
        };

        $metricIds = Metric::query()
            ->whereIn('key', $metricKeys)
            ->pluck('id', 'key');

        $syncData = [];

        foreach ($metricKeys as $index => $metricKey) {
            if (! isset($metricIds[$metricKey])) {
                continue;
            }

            $syncData[$metricIds[$metricKey]] = [
                'is_required' => in_array($metricKey, $requiredMetricKeys, true),
                'is_primary' => $index === 0,
                'sort_order' => $index + 1,
            ];
        }

        $exercise->metrics()->sync($syncData);
    }
}
