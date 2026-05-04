<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\ExerciseCategory;
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
            ->with(['sport:id,name', 'category:id,name'])
            ->orderBy('sport_id')
            ->orderBy('name')
            ->get(['id', 'name', 'sport_id', 'exercise_category_id', 'user_id']);

        $workoutSessions = WorkoutSession::query()
            ->where('user_id', $user->id)
            ->with([
                'exercises' => fn ($query) => $query
                    ->select(['exercises.id', 'exercises.name', 'sport_id', 'exercise_category_id'])
                    ->with(['sport:id,name'])
                    ->orderBy('workout_session_exercise.position'),
            ])
            ->latest()
            ->get(['id', 'name', 'description', 'user_id', 'created_at']);

        $performedSessions = PerformedSession::query()
            ->where('user_id', $user->id)
            ->with([
                'workoutSession:id,name',
                'performances:id,performed_session_id,exercise_id,weight,repetitions,duration_minutes,distance_meters',
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
                'sport_id' => $exercise->sport_id,
                'sport_name' => $exercise->sport?->name,
                'category_name' => $exercise->category?->name,
                'is_custom' => $exercise->user_id !== null,
            ]),
            'workoutSessions' => $workoutSessions->map(fn ($session) => [
                'id' => $session->id,
                'name' => $session->name,
                'description' => $session->description,
                'created_at' => $session->created_at?->toISOString(),
                'exercises' => $session->exercises->map(fn ($exercise) => [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'sport_id' => $exercise->sport_id,
                    'sport_name' => $exercise->sport?->name,
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
            'Seance type creee avec succes.',
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

        Exercise::query()->create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'sport_id' => $validatedData['sport_id'],
            'exercise_category_id' => $validatedData['exercise_category_id'],
            'user_id' => $request->user()->id,
        ]);

        return to_route('sessions')->with(
            'success',
            'Exercice personnalise cree avec succes.',
        );
    }

    public function storePerformedSession(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'workout_session_id' => [
                'required',
                'integer',
                Rule::exists('workout_sessions', 'id')->where(
                    fn ($query) => $query->where('user_id', $request->user()->id),
                ),
            ],
            'performed_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        PerformedSession::query()->create([
            'user_id' => $request->user()->id,
            'workout_session_id' => $validatedData['workout_session_id'],
            'performed_at' => $validatedData['performed_at'],
            'notes' => $validatedData['notes'] ?? null,
        ]);

        return to_route('sessions')->with(
            'success',
            'Seance ajoutee au calendrier.',
        );
    }

    public function completePerformedSession(Request $request, PerformedSession $performedSession): RedirectResponse
    {
        if ($performedSession->user_id !== $request->user()->id) {
            throw new AuthorizationException();
        }

        if ($performedSession->completed_at !== null) {
            return back()->withErrors([
                'performed_session_id' => 'Cette seance est deja validee.',
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
        ]);

        $workoutExerciseIds = $performedSession->workoutSession()
            ->firstOrFail()
            ->exercises()
            ->pluck('exercises.id')
            ->all();

        $submittedPerformances = collect($validatedData['performances'] ?? [])
            ->filter(function (array $performance) {
                return collect([
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

        DB::transaction(function () use ($request, $performedSession, $validatedData, $submittedPerformances) {
            $performedSession->update([
                'completed_at' => now(),
                'notes' => $validatedData['notes'] ?? $performedSession->notes,
            ]);

            foreach ($submittedPerformances as $performanceData) {
                Performance::query()->updateOrCreate(
                    [
                        'performed_session_id' => $performedSession->id,
                        'exercise_id' => $performanceData['exercise_id'],
                    ],
                    [
                        'user_id' => $request->user()->id,
                        'performed_at' => $performedSession->performed_at,
                        'weight' => $performanceData['weight'] ?? null,
                        'repetitions' => $performanceData['repetitions'] ?? null,
                        'duration_minutes' => $performanceData['duration_minutes'] ?? null,
                        'distance_meters' => $performanceData['distance_meters'] ?? null,
                    ],
                );
            }
        });

        return to_route('sessions')->with(
            'success',
            'Seance validee avec succes.',
        );
    }
}
