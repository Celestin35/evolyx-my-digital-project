<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'ads_enabled',
        'premium_features',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'ads_enabled' => 'boolean',
            'premium_features' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
