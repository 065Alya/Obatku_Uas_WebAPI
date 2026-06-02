<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'key',
        'abilities',
        'allowed_ips',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'abilities'      => 'array',
        'allowed_ips'    => 'array',
        'last_used_at'   => 'datetime',
        'expires_at'     => 'datetime',
        'is_active'      => 'boolean',
    ];

    protected $hidden = ['key'];

    /* ─── Relationships ─── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ─── Scopes ─── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    /* ─── Helpers ─── */

    /**
     * Check if this key has a given ability.
     */
    public function can(string $ability): bool
    {
        if (empty($this->abilities)) return true; // no restrictions = full access
        return in_array($ability, $this->abilities, true)
            || in_array('*', $this->abilities, true);
    }

    /**
     * Check if the requesting IP is allowed.
     */
    public function allowsIp(string $ip): bool
    {
        if (empty($this->allowed_ips)) return true; // no restriction
        return in_array($ip, $this->allowed_ips, true);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Touch the last_used_at timestamp efficiently.
     */
    public function recordUsage(): void
    {
        $this->timestamps = false;
        $this->update(['last_used_at' => now()]);
        $this->timestamps = true;
    }

    /**
     * Generate a new secure API key string (unhashed).
     * Call before saving; store only the hash.
     */
    public static function generateKey(string $prefix = 'obatku'): string
    {
        return $prefix . '_' . Str::random(48);
    }
}
