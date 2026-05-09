<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceMetricValue extends Model
{
    protected $fillable = [
        'performance_id',
        'metric_id',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    public function performance(): BelongsTo
    {
        return $this->belongsTo(Performance::class);
    }

    public function metric(): BelongsTo
    {
        return $this->belongsTo(Metric::class);
    }
}
