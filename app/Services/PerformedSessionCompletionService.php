<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\PerformedSession;
use App\Models\User;
use App\Models\WorkoutSession;
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

        if (
            $workoutSession->user_id === null &&
            $workoutSession->exercises->pluck('sport_id')->diff($userSportIds)->isNotEmpty()
        ) {
            return ['workout_session_id' => 'Cette sÃ©ance type ne correspond pas Ã  vos sports.'];
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
        if ($performedSession->completed_at !== null) {
            return ['performed_session_id' => 'Cette sÃ©ance est dÃ©jÃ  validÃ©e.'];
        }

        if ($performedSession->performed_at->copy()->startOfDay()->isAfter(today())) {
            return ['performed_session_id' => 'Vous pouvez valider uniquement une sÃ©ance prÃ©vue aujourdâ€™hui ou avant.'];
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
            return ['performances' => 'Renseignez au moins une performance ou validez la sÃ©ance sans performances.'];
        }

        if ($submittedPerformances->pluck('exercise_id')->diff($workoutExerciseIds)->isNotEmpty()) {
            return ['performances' => 'Certains exercices ne font pas partie de cette sÃ©ance.'];
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
                return ['performances' => 'Certaines mÃ©triques ne correspondent pas Ã  cet exercice.'];
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
