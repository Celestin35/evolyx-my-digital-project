<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class WeightEntry extends Model
{
    protected $fillable = [
        'weight',
        'body_fat',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'body_fat' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
