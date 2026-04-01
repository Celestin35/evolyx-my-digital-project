<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Macronutrient extends Model
{
    protected $fillable = [
        'fats',
        'carbs',
        'protein',
    ];

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }
}
