<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\Metric;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ExerciseService
{
    public function create(User $user, array $data): Exercise
    {
        $exercise = Exercise::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sport_id' => $data['sport_id'],
            'exercise_category_id' => $data['exercise_category_id'],
            'user_id' => $user->id,
        ]);

        $this->attachDefaultMetrics($exercise);

        return $exercise;
    }

    public function update(User $user, Exercise $exercise, array $data): void
    {
        $this->authorizeOwner($user, $exercise);

        $exercise->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sport_id' => $data['sport_id'],
            'exercise_category_id' => $data['exercise_category_id'],
        ]);

        $this->attachDefaultMetrics($exercise);
    }

    public function delete(User $user, Exercise $exercise): ?array
    {
        $this->authorizeOwner($user, $exercise);

        if ($exercise->performances()->exists() || $exercise->workoutSessions()->exists()) {
            return ['exercise' => 'Cet exercice est déjà utilisé dans une séance.'];
        }

        DB::transaction(function () use ($exercise) {
            $exercise->metrics()->detach();
            $exercise->delete();
        });

        return null;
    }

    private function authorizeOwner(User $user, Exercise $exercise): void
    {
        if ($exercise->user_id !== $user->id) {
            throw new AuthorizationException;
        }
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

        $metricIds = Metric::query()
            ->whereIn('key', $metricKeys)
            ->pluck('id', 'key');

        $syncData = [];

        foreach ($metricKeys as $index => $metricKey) {
            if (! isset($metricIds[$metricKey])) {
                continue;
            }

            $syncData[$metricIds[$metricKey]] = [
                'sort_order' => $index + 1,
            ];
        }

        $exercise->metrics()->sync($syncData);
    }
}
