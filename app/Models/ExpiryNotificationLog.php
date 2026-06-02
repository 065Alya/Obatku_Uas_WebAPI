<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpiryNotificationLog extends Model
{
    use HasFactory;

    protected $table = 'expiry_notification_logs';

    protected $fillable = [
        'user_id',
        'medicine_id',
        'channel',
        'expiry_date',
        'days_threshold',
        'resend_after',
        'sent_at',
    ];

    protected $casts = [
        'expiry_date'   => 'date',
        'resend_after'  => 'datetime',
        'sent_at'       => 'datetime',
    ];

    /* ─── Relationships ─── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /* ─── Scopes ─── */

    /** Logs due for re-sending (resend_after has passed or is null). */
    public function scopeDueForResend(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('resend_after')
              ->orWhere('resend_after', '<=', now());
        });
    }

    /* ─── Static Helpers ─── */

    /**
     * Check whether a notification has already been sent and is not yet due
     * for re-sending. Used by the scheduled command to skip duplicates.
     */
    public static function alreadySent(
        int    $userId,
        int    $medicineId,
        int    $daysThreshold,
        string $channel = 'database'
    ): bool {
        return self::where('user_id',       $userId)
            ->where('medicine_id',          $medicineId)
            ->where('days_threshold',       $daysThreshold)
            ->where('channel',              $channel)
            ->where(function ($q) {
                $q->whereNull('resend_after')
                  ->orWhere('resend_after', '>', now());
            })
            ->exists();
    }

    /**
     * Record a sent notification. Upserts so duplicates are prevented.
     */
    public static function record(
        int    $userId,
        int    $medicineId,
        string $channel,
        string $expiryDate,
        int    $daysThreshold,
        int    $resendAfterDays = 7
    ): self {
        return self::updateOrCreate(
            [
                'user_id'        => $userId,
                'medicine_id'    => $medicineId,
                'days_threshold' => $daysThreshold,
                'channel'        => $channel,
            ],
            [
                'expiry_date'  => $expiryDate,
                'resend_after' => now()->addDays($resendAfterDays),
                'sent_at'      => now(),
            ]
        );
    }
}
