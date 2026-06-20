<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\PerformedSession;
use App\Models\User;
use App\Models\WorkoutSession;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class PerformedSessionCompletionService
{
    public function __construct(
        private readonly PerformanceRecordingService $performanceRecordingService,
    ) {}

    public function create(User $user, array $data): ?array
    {
        $userSportIds = $user->sports()->pluck('sports.id');
        $workoutSession = WorkoutSession::query()
            ->with('exercises:id,sport_id')
            ->findOrFail($data['workout_session_id']);

        if ($workoutSession->user_id !== $user->id && $workoutSession->user_id !== null) {
            throw new AuthorizationException;
        }

        if (
            $workoutSession->user_id === null &&
            $workoutSession->exercises->pluck('sport_id')->diff($userSportIds)->isNotEmpty()
        ) {
            return ['workout_session_id' => 'Cette séance type ne correspond pas à vos sports.'];
        }

        PerformedSession::query()->create([
            'user_id' => $user->id,
            'workout_session_id' => $data['workout_session_id'],
            'performed_at' => $data['performed_at'],
            'notes' => $data['notes'] ?? null,
        ]);

        return null;
    }

    public function complete(User $user, PerformedSession $performedSession, array $data): ?array
    {
        if ($performedSession->user_id !== $user->id) {
            throw new AuthorizationException;
        }

        if ($performedSession->completed_at !== null) {
            return ['performed_session_id' => 'Cette séance est déjà validée.'];
        }

        if ($performedSession->performed_at->copy()->startOfDay()->isAfter(today())) {
            return ['performed_session_id' => 'Vous pouvez valider uniquement une séance prévue aujourd’hui ou avant.'];
        }

        $workoutExerciseIds = $performedSession->workoutSession()
            ->firstOrFail()
            ->exercises()
            ->pluck('exercises.id')
            ->all();

        $submittedPerformances = collect($data['performances'] ?? [])
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

        if (($data['performances'] ?? []) !== [] && $submittedPerformances->isEmpty()) {
            return ['performances' => 'Renseignez au moins une performance ou validez la séance sans performances.'];
        }

        if ($submittedPerformances->pluck('exercise_id')->diff($workoutExerciseIds)->isNotEmpty()) {
            return ['performances' => 'Certains exercices ne font pas partie de cette séance.'];
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
                return ['performances' => 'Certaines métriques ne correspondent pas à cet exercice.'];
            }
        }

        DB::transaction(function () use ($user, $performedSession, $data, $submittedPerformances) {
            $performedSession->update([
                'completed_at' => now(),
                'notes' => $data['notes'] ?? $performedSession->notes,
            ]);

            $this->performanceRecordingService->record($user, $performedSession, $submittedPerformances);
        });

        return null;
    }
}
