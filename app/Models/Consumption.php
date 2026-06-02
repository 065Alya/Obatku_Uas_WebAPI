<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'medicine_id',
        'medicine_schedule_id',
        'schedule_log_id',
        'family_member_id',
        'quantity',
        'unit',
        'status',
        'notes',
        'consumed_at',
        'is_synced',
        'offline_id',
    ];

    protected $casts = [
        'quantity'     => 'decimal:2',
        'is_synced'    => 'boolean',
        'consumed_at'  => 'datetime',
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

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MedicineSchedule::class, 'medicine_schedule_id');
    }

    public function scheduleLog(): BelongsTo
    {
        return $this->belongsTo(ScheduleLog::class, 'schedule_log_id');
    }

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    /* ─── Scopes ─── */

    /** Only consumptions not yet synced to the server. */
    public function scopeUnsynced(Builder $query): Builder
    {
        return $query->where('is_synced', false);
    }

    /** Consumptions within a date range. */
    public function scopeInPeriod(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('consumed_at', [$from, $to]);
    }

    /** Consumptions by status. */
    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /* ─── Helpers ─── */

    public function isTaken(): bool  { return $this->status === 'taken'; }
    public function isSkipped(): bool { return $this->status === 'skipped'; }
    public function isMissed(): bool  { return $this->status === 'missed'; }

    /**
     * Calculate adherence rate (%) for a given user over a date range.
     *
     * @return float  0–100
     */
    public static function adherenceRate(int $userId, string $from, string $to): float
    {
        $total = self::where('user_id', $userId)
            ->whereBetween('consumed_at', [$from, $to])
            ->whereIn('status', ['taken', 'skipped', 'missed'])
            ->count();

        if ($total === 0) return 0.0;

        $taken = self::where('user_id', $userId)
            ->whereBetween('consumed_at', [$from, $to])
            ->where('status', 'taken')
            ->count();

        return round(($taken / $total) * 100, 1);
    }
}
