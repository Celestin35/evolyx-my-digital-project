<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformedSession extends Model
{
    protected $fillable = [
        'user_id',
        'workout_session_id',
        'performed_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workoutSession(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class);
    }

    public function performances(): HasMany
    {
        return $this->hasMany(Performance::class);
    }
}
