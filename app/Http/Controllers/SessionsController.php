<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sessions\CompletePerformedSessionRequest;
use App\Http\Requests\Sessions\StoreExerciseRequest;
use App\Http\Requests\Sessions\StorePerformedSessionRequest;
use App\Http\Requests\Sessions\StoreWorkoutSessionRequest;
use App\Http\Requests\Sessions\UpdateExerciseRequest;
use App\Http\Requests\Sessions\UpdateWorkoutSessionRequest;
use App\Models\Exercise;
use App\Models\PerformedSession;
use App\Models\WorkoutSession;
use App\Services\ExerciseService;
use App\Services\PerformedSessionCompletionService;
use App\Services\SessionPageDataService;
use App\Services\WorkoutSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SessionsController extends Controller
{
    public function show(Request $request, SessionPageDataService $sessionPageDataService): Response
    {
        return Inertia::render('Sessions', $sessionPageDataService->getForUser($request->user()));
    }

    public function storeWorkoutSession(
        StoreWorkoutSessionRequest $request,
        WorkoutSessionService $workoutSessionService,
    ): RedirectResponse {
        $user = $request->user();
        $validatedData = $request->validated();

        if ($errors = $workoutSessionService->create($user, $validatedData)) {
            return back()->withErrors($errors);
        }

        return to_route('sessions')->with(
            'success',
            'Séance type créée avec succès.',
        );
    }

    public function updateWorkoutSession(
        UpdateWorkoutSessionRequest $request,
        WorkoutSession $workoutSession,
        WorkoutSessionService $workoutSessionService,
    ): RedirectResponse {
        Gate::authorize('update', $workoutSession);

        $validatedData = $request->validated();

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
        Gate::authorize('delete', $workoutSession);

        if ($errors = $workoutSessionService->delete($request->user(), $workoutSession)) {
            return back()->withErrors($errors);
        }

        return to_route('sessions')->with(
            'success',
            'Séance type supprimée avec succès.',
        );
    }

    public function storeExercise(StoreExerciseRequest $request, ExerciseService $exerciseService): RedirectResponse
    {
        $validatedData = $request->validated();

        $exerciseService->create($request->user(), $validatedData);

        return to_route('sessions')->with(
            'success',
            'Exercice personnalisé créé avec succès.',
        );
    }

    public function updateExercise(
        UpdateExerciseRequest $request,
        Exercise $exercise,
        ExerciseService $exerciseService,
    ): RedirectResponse {
        Gate::authorize('update', $exercise);

        $validatedData = $request->validated();

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
        Gate::authorize('delete', $exercise);

        if ($errors = $exerciseService->delete($request->user(), $exercise)) {
            return back()->withErrors($errors);
        }

        return to_route('sessions')->with(
            'success',
            'Exercice personnalisé supprimé avec succès.',
        );
    }

    public function storePerformedSession(
        StorePerformedSessionRequest $request,
        PerformedSessionCompletionService $performedSessionService,
    ): RedirectResponse {
        $validatedData = $request->validated();
        $workoutSession = WorkoutSession::query()->findOrFail($validatedData['workout_session_id']);

        Gate::authorize('schedule', $workoutSession);

        if ($errors = $performedSessionService->create($request->user(), $validatedData)) {
            return back()->withErrors($errors);
        }

        return to_route('sessions')->with(
            'success',
            'Séance ajoutée au calendrier.',
        );
    }

    public function completePerformedSession(
        CompletePerformedSessionRequest $request,
        PerformedSession $performedSession,
        PerformedSessionCompletionService $performedSessionService,
    ): RedirectResponse {
        Gate::authorize('complete', $performedSession);

        $validatedData = $request->validated();

        if ($errors = $performedSessionService->complete($request->user(), $performedSession, $validatedData)) {
            return back()->withErrors($errors);
        }

        return to_route('sessions')->with(
            'success',
            'Séance validée avec succès.',
        );
    }
}
