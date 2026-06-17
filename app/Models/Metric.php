<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Metric extends Model
{
    protected $fillable = [
        'key',
        'label',
        'unit',
        'value_type',
        'sort_order',
    ];

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'exercise_metric')
            ->withPivot(['sort_order']);
    }

    public function performanceValues(): HasMany
    {
        return $this->hasMany(PerformanceMetricValue::class);
    }
}
