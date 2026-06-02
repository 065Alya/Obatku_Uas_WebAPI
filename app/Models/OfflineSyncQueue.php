<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflineSyncQueue extends Model
{
    use HasFactory;

    protected $table = 'offline_sync_queue';

    protected $fillable = [
        'user_id',
        'client_id',
        'entity_type',
        'action',
        'payload',
        'http_status',
        'error_message',
        'status',
        'attempts',
        'performed_at',
        'synced_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'performed_at' => 'datetime',
        'synced_at'    => 'datetime',
    ];

    /* ─── Constants ─── */

    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SYNCED     = 'synced';
    public const STATUS_FAILED     = 'failed';

    public const MAX_ATTEMPTS = 3;

    /* ─── Relationships ─── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ─── Scopes ─── */

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRetryable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED)
                     ->where('attempts', '<', self::MAX_ATTEMPTS);
    }

    /* ─── Helpers ─── */

    public function isPending(): bool    { return $this->status === self::STATUS_PENDING; }
    public function isSynced(): bool     { return $this->status === self::STATUS_SYNCED; }
    public function isFailed(): bool     { return $this->status === self::STATUS_FAILED; }
    public function canRetry(): bool     { return $this->isFailed() && $this->attempts < self::MAX_ATTEMPTS; }

    /**
     * Mark the entry as successfully synced.
     */
    public function markSynced(int $httpStatus = 200): void
    {
        $this->update([
            'status'     => self::STATUS_SYNCED,
            'http_status'=> $httpStatus,
            'synced_at'  => now(),
        ]);
    }

    /**
     * Mark the entry as failed, incrementing the attempt counter.
     */
    public function markFailed(string $errorMessage, int $httpStatus = 0): void
    {
        $this->increment('attempts');
        $this->update([
            'status'        => self::STATUS_FAILED,
            'http_status'   => $httpStatus,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Idempotency check — has this client_id already been processed?
     */
    public static function alreadyProcessed(string $clientId): bool
    {
        return self::where('client_id', $clientId)
                   ->where('status', self::STATUS_SYNCED)
                   ->exists();
    }
}
