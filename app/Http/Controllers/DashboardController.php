<?php

namespace App\Http\Controllers;

use App\Models\PerformedSession;
use App\Models\Performance;
use App\Services\WeightEntriesService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, WeightEntriesService $weightEntriesService): Response
    {
        $recentPerformedSessions = PerformedSession::query()
            ->where('user_id', $request->user()->id)
            ->whereNotNull('completed_at')
            ->with('workoutSession:id,name')
            ->withCount('performances')
            ->latest('performed_at')
            ->limit(6)
            ->get(['id', 'workout_session_id', 'performed_at', 'completed_at', 'notes']);

        $recentPerformances = Performance::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'exercise:id,name,sport_id',
                'exercise.sport:id,name',
            ])
            ->latest('performed_at')
            ->limit(5)
            ->get([
                'id',
                'performed_at',
                'weight',
                'repetitions',
                'duration_minutes',
                'distance_meters',
                'exercise_id',
            ]);

        return Inertia::render('Dashboard', [
            'weightEntries' => $weightEntriesService->getForUser($request->user()),
            'recentPerformedSessions' => $recentPerformedSessions->map(fn ($session) => [
                'id' => $session->id,
                'workout_session_name' => $session->workoutSession?->name ?? 'Séance',
                'performed_at' => $session->performed_at?->toISOString(),
                'completed_at' => $session->completed_at?->toISOString(),
                'performances_count' => $session->performances_count,
                'notes' => $session->notes,
            ]),
            'recentPerformances' => $recentPerformances->map(fn ($performance) => [
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
                'exercise_name' => $performance->exercise?->name ?? 'Exercice',
                'sport_name' => $performance->exercise?->sport?->name,
            ]),
        ]);
    }
}
