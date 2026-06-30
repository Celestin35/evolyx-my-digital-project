<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\PerformedSession;
use App\Models\Sport;
use App\Models\User;
use App\Models\WorkoutSession;

class SessionPageDataService
{
    public function __construct(
        private readonly FeatureAccessService $featureAccessService,
    ) {}

    public function getForUser(User $user): array
    {
        $userSportIds = $user->sports()->pluck('sports.id');

        $sports = Sport::query()
            ->whereIn('id', $userSportIds)
            ->orderBy('sort_order')
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
                'workoutSession:id,name,description,user_id,created_at',
                'workoutSession.exercises' => fn ($query) => $query
                    ->select(['exercises.id', 'exercises.name', 'sport_id', 'exercise_category_id'])
                    ->with(['sport:id,name', 'metrics:id,key,label,unit,value_type'])
                    ->orderBy('workout_session_exercise.position'),
                'performances:id,performed_session_id,exercise_id,weight,repetitions,duration_minutes,distance_meters',
                'performances.metricValues.metric:id,key,label,unit,value_type',
                'communityPost:id,performed_session_id',
            ])
            ->orderBy('performed_at')
            ->get(['id', 'user_id', 'workout_session_id', 'performed_at', 'completed_at', 'notes']);

        return [
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
                'workout_session' => $performedSession->workoutSession ? [
                    'id' => $performedSession->workoutSession->id,
                    'name' => $performedSession->workoutSession->name,
                    'description' => $performedSession->workoutSession->description,
                    'created_at' => $performedSession->workoutSession->created_at?->toISOString(),
                    'is_system' => $performedSession->workoutSession->user_id === null,
                    'exercises' => $performedSession->workoutSession->exercises->map(fn ($exercise) => [
                        'id' => $exercise->id,
                        'name' => $exercise->name,
                        'sport_id' => $exercise->sport_id,
                        'sport_name' => $exercise->sport?->name,
                        'metrics' => $this->formatExerciseMetrics($exercise),
                    ])->values(),
                ] : null,
                'performed_at' => $performedSession->performed_at?->toISOString(),
                'completed_at' => $performedSession->completed_at?->toISOString(),
                'community_post_id' => $performedSession->communityPost?->id,
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
            'canShareToCommunity' => $this->featureAccessService->canShareCommunityPost($user),
        ];
    }

    private function formatExerciseMetrics(Exercise $exercise): array
    {
        return $exercise->metrics
            ->map(fn ($metric) => [
                'key' => $metric->key,
                'label' => $metric->label,
                'unit' => $metric->unit,
                'value_type' => $metric->value_type,
                'sort_order' => $metric->pivot->sort_order,
            ])
            ->values()
            ->all();
    }
}
