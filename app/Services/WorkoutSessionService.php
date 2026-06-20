<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutSession;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class WorkoutSessionService
{
    public function create(User $user, array $data): ?array
    {
        if (! $this->exerciseIdsBelongToUserSports($user, $data['exercise_ids'])) {
            return ['exercise_ids' => 'Certains exercices ne correspondent pas à vos sports.'];
        }

        DB::transaction(function () use ($user, $data) {
            $workoutSession = WorkoutSession::query()->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'user_id' => $user->id,
            ]);

            $workoutSession->exercises()->attach($this->exercisePivotData($data['exercise_ids']));
        });

        return null;
    }

    public function update(User $user, WorkoutSession $workoutSession, array $data): ?array
    {
        $this->authorizeOwner($user, $workoutSession);

        if (! $this->exerciseIdsBelongToUserSports($user, $data['exercise_ids'])) {
            return ['exercise_ids' => 'Certains exercices ne correspondent pas à vos sports.'];
        }

        DB::transaction(function () use ($workoutSession, $data) {
            $workoutSession->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            $workoutSession->exercises()->sync($this->exercisePivotData($data['exercise_ids']));
        });

        return null;
    }

    public function delete(User $user, WorkoutSession $workoutSession): ?array
    {
        $this->authorizeOwner($user, $workoutSession);

        if ($workoutSession->performedSessions()->exists()) {
            return ['workout_session' => 'Cette séance type est déjà utilisée dans le calendrier.'];
        }

        DB::transaction(function () use ($workoutSession) {
            $workoutSession->exercises()->detach();
            $workoutSession->delete();
        });

        return null;
    }

    private function authorizeOwner(User $user, WorkoutSession $workoutSession): void
    {
        if ($workoutSession->user_id !== $user->id) {
            throw new AuthorizationException;
        }
    }

    private function exerciseIdsBelongToUserSports(User $user, array $exerciseIds): bool
    {
        $userSportIds = $user->sports()->pluck('sports.id');

        return Exercise::query()
            ->whereIn('id', $exerciseIds)
            ->whereIn('sport_id', $userSportIds)
            ->count() === count(array_unique($exerciseIds));
    }

    private function exercisePivotData(array $exerciseIds): array
    {
        $exercisePivotData = [];

        foreach (array_values(array_unique($exerciseIds)) as $index => $exerciseId) {
            $exercisePivotData[$exerciseId] = [
                'position' => $index + 1,
                'rest_time' => null,
            ];
        }

        return $exercisePivotData;
    }
}
