<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'action',
        'description',
        'ip_address',
        'user_agent',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
