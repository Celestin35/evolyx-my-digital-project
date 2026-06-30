<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $fillable = [
        'target_weight',
        'weekly_weight_goal',
        'daily_calories',
        'is_active',
        'goal_end_date',
        'macronutrient_id',
        'goal_type_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'macronutrient_id' => 'integer',
            'goal_type_id' => 'integer',
            'target_weight' => 'decimal:2',
            'weekly_weight_goal' => 'decimal:2',
            'is_active' => 'boolean',
            'goal_end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goalType(): BelongsTo
    {
        return $this->belongsTo(GoalType::class);
    }

    public function macronutrient(): BelongsTo
    {
        return $this->belongsTo(Macronutrient::class);
    }
}
