<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $fillable = [
        'name',
        'description',
        'exercise_category_id',
        'sport_id',
        'user_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExerciseCategory::class, 'exercise_category_id');
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'exercise_equipment');
    }

    public function metrics(): BelongsToMany
    {
        return $this->belongsToMany(Metric::class, 'exercise_metric')
            ->withPivot(['is_required', 'is_primary', 'sort_order'])
            ->orderBy('exercise_metric.sort_order');
    }

    public function performances(): HasMany
    {
        return $this->hasMany(Performance::class);
    }

    public function workoutSessions(): BelongsToMany
    {
        return $this->belongsToMany(WorkoutSession::class, 'workout_session_exercise');
    }
}
