<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    use HasFactory;

    protected $table = 'push_subscriptions';

    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh_key',
        'auth_token',
        'device_name',
        'user_agent',
        'is_active',
        'last_notified_at',
    ];

    protected $hidden = [
        'p256dh_key',
        'auth_token',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'last_notified_at' => 'datetime',
    ];

    /* ─── Relationships ─── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ─── Scopes ─── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /* ─── Helpers ─── */

    /**
     * Returns the subscription as a Minishlink\WebPush\Subscription-compatible array.
     */
    public function toWebPushArray(): array
    {
        return [
            'endpoint'        => $this->endpoint,
            'publicKey'       => $this->p256dh_key,
            'authToken'       => $this->auth_token,
            'contentEncoding' => 'aesgcm',
        ];
    }

    /**
     * Register or update a push subscription for a user.
     */
    public static function upsertForUser(int $userId, array $data): self
    {
        return self::updateOrCreate(
            [
                'user_id'  => $userId,
                'endpoint' => $data['endpoint'],
            ],
            [
                'p256dh_key'  => $data['p256dh_key'],
                'auth_token'  => $data['auth_token'],
                'device_name' => $data['device_name'] ?? null,
                'user_agent'  => $data['user_agent']  ?? null,
                'is_active'   => true,
            ]
        );
    }

    /**
     * Mark the subscription as invalid (e.g., browser revoked permission).
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Touch the last_notified_at timestamp after sending a push.
     */
    public function recordNotified(): void
    {
        $this->timestamps = false;
        $this->update(['last_notified_at' => now()]);
        $this->timestamps = true;
    }
}
