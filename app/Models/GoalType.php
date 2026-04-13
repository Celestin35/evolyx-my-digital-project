<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class GoalType extends Model
{
    protected $fillable = [
        'name',
    ];

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }
}
