<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'avatar',
        'date_of_birth',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /* ─── Role Helpers ─── */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /* ─── Relationships ─── */

    public function profile(): HasOne
    {
        return $this->hasOne(PersonalProfile::class);
    }

    public function families(): HasMany
    {
        return $this->hasMany(Family::class);
    }

    public function familyMembers(): HasManyThrough
    {
        return $this->hasManyThrough(FamilyMember::class, Family::class);
    }

    public function medicines(): MorphMany
    {
        return $this->morphMany(Medicine::class, 'owner');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(MedicineSchedule::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(Consumption::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function expiryNotificationLogs(): HasMany
    {
        return $this->hasMany(ExpiryNotificationLog::class);
    }

    public function offlineSyncQueue(): HasMany
    {
        return $this->hasMany(OfflineSyncQueue::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /* ─── Computed Helpers ─── */

    /**
     * Unread alert count (cached for the request lifecycle).
     */
    public function getUnreadAlertsCountAttribute(): int
    {
        return $this->alerts()->unread()->count();
    }
}
