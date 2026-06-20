<?php

namespace App\Models;

use App\Services\FeatureAccessService;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $appends = [
        'name',
    ];

    protected $fillable = [
        'email',
        'pseudo',
        'password',
        'first_name',
        'sex',
        'height',
        'activity_level',
        'birth_date',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function weightEntries(): HasMany
    {
        return $this->hasMany(WeightEntry::class);
    }

    public function latestWeightEntry(): HasOne
    {
        return $this->hasOne(WeightEntry::class)->latestOfMany();
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function communityPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }

    public function workoutSessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class);
    }

    public function performances(): HasMany
    {
        return $this->hasMany(Performance::class);
    }

    public function performedSessions(): HasMany
    {
        return $this->hasMany(PerformedSession::class);
    }

    public function securityLogs(): HasMany
    {
        return $this->hasMany(SecurityLog::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class);
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_follows',
            'follower_id',
            'followed_id',
        )->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_follows',
            'followed_id',
            'follower_id',
        )->withTimestamps();
    }

    public function hasPremiumFeatures(): bool
    {
        return app(FeatureAccessService::class)->hasPremiumFeatures($this);
    }

    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->birth_date?->age,
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->first_name,
        );
    }

    protected function currentWeight(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->relationLoaded('latestWeightEntry')
                ? $this->latestWeightEntry?->weight
                : $this->latestWeightEntry()->value('weight'),
        );
    }
}
