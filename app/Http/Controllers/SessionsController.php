<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\PerformedSession;
use App\Models\WorkoutSession;
use App\Services\ExerciseService;
use App\Services\PerformedSessionCompletionService;
use App\Services\SessionPageDataService;
use App\Services\WorkoutSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SessionsController extends Controller
{
    public function show(Request $request, SessionPageDataService $sessionPageDataService): Response
    {
        return Inertia::render('Sessions', $sessionPageDataService->getForUser($request->user()));
    }

    public function storeWorkoutSession(
        Request $request,
        WorkoutSessionService $workoutSessionService,
    ): RedirectResponse {
        $user = $request->user();

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

        if ($errors = $workoutSessionService->create($user, $validatedData)) {
            return back()->withErrors($errors);
        }

        return to_route('sessions')->with(
            'success',
            'Séance type créée avec succès.',
        );
    }

    public function updateWorkoutSession(
        Request $request,
        WorkoutSession $workoutSession,
        WorkoutSessionService $workoutSessionService,
    ): RedirectResponse {
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

        if ($errors = $workoutSessionService->update($request->user(), $workoutSession, $validatedData)) {
            return back()->withErrors($errors);
        }

        return to_route('sessions')->with(
            'success',
            'Séance type mise à jour avec succès.',
        );
    }

    public function destroyWorkoutSession(
        Request $request,
        WorkoutSession $workoutSession,
        WorkoutSessionService $workoutSessionService,
    ): RedirectResponse {
        if ($errors = $workoutSessionService->delete($request->user(), $workoutSession)) {
            return back()->withErrors($errors);
        }

        return to_route('sessions')->with(
            'success',
            'Séance type supprimée avec succès.',
        );
    }

    public function storeExercise(Request $request, ExerciseService $exerciseService): RedirectResponse
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

        $exerciseService->create($request->user(), $validatedData);

        return to_route('sessions')->with(
            'success',
            'Exercice personnalisé créé avec succès.',
        );
    }

    public function updateExercise(
        Request $request,
        Exercise $exercise,
        ExerciseService $exerciseService,
    ): RedirectResponse {
        $userSportIds = $request->user()->sports()->pluck('sports.id');

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sport_id' => ['required', 'integer', Rule::exists('sports', 'id')->where(
                fn ($query) => $query->whereIn('id', $userSportIds),
            )],
            'exercise_category_id' => ['required', 'integer', Rule::exists('exercise_categories', 'id')],
        ]);

        $exerciseService->update($request->user(), $exercise, $validatedData);

        return to_route('sessions')->with(
            'success',
            'Exercice personnalisé mis à jour avec succès.',
        );
    }

    public function destroyExercise(
        Request $request,
        Exercise $exercise,
        ExerciseService $exerciseService,
    ): RedirectResponse {
        if ($errors = $exerciseService->delete($request->user(), $exercise)) {
            return back()->withErrors($errors);
        }

        return to_route('sessions')->with(
            'success',
            'Exercice personnalisé supprimé avec succès.',
        );
    }

    public function storePerformedSession(
        Request $request,
        PerformedSessionCompletionService $performedSessionService,
    ): RedirectResponse {
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

        if ($errors = $performedSessionService->create($request->user(), $validatedData)) {
            return back()->withErrors($errors);
        }

        return to_route('sessions')->with(
            'success',
            'Séance ajoutée au calendrier.',
        );
    }

    public function completePerformedSession(
        Request $request,
        PerformedSession $performedSession,
        PerformedSessionCompletionService $performedSessionService,
    ): RedirectResponse {
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

        if ($errors = $performedSessionService->complete($request->user(), $performedSession, $validatedData)) {
            return back()->withErrors($errors);
        }

        return to_route('sessions')->with(
            'success',
            'Séance validée avec succès.',
        );
    }
}
