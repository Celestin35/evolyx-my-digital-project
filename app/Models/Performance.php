<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Performance extends Model
{
    protected $fillable = [
        'performed_at',
        'weight',
        'repetitions',
        'duration_minutes',
        'distance_meters',
        'exercise_id',
        'performed_session_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
            'weight' => 'decimal:2',
            'duration_minutes' => 'decimal:2',
            'distance_meters' => 'decimal:2',
        ];
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function performedSession(): BelongsTo
    {
        return $this->belongsTo(PerformedSession::class);
    }

    public function metricValues(): HasMany
    {
        return $this->hasMany(PerformanceMetricValue::class);
    }
}
