<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    protected $fillable = [
        'name',
        'display_name',
    ];

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
