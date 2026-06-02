<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Alert extends Model
{
    use HasFactory;

    /* ─── Constants ─────────────────────────────────────────────────── */

    // Alert types
    public const TYPE_INTERACTION = 'interaction';
    public const TYPE_STOCK       = 'stock';
    public const TYPE_REMINDER    = 'reminder';

    // Severity levels
    public const SEVERITY_INFO    = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_DANGER  = 'danger';

    /* ─── Fillable ───────────────────────────────────────────────────── */

    protected $fillable = [
        'user_id',
        'type',
        'severity',
        'message',
        'is_read',
        'alertable_type',
        'alertable_id',
    ];

    /* ─── Casts ──────────────────────────────────────────────────────── */

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ─── Relationships ──────────────────────────────────────────────── */

    /**
     * The user this alert belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic relation to the originating entity
     * (Medicine, MedicineSchedule, MedicineInteraction, etc.)
     */
    public function alertable(): MorphTo
    {
        return $this->morphTo();
    }

    /* ─── Local Scopes ───────────────────────────────────────────────── */

    /** Only unread alerts. */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /** Only read alerts. */
    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    /** Filter by a specific type. */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /** Filter by severity. */
    public function scopeOfSeverity(Builder $query, string $severity): Builder
    {
        return $query->where('severity', $severity);
    }

    /** Interaction alerts only. */
    public function scopeInteraction(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_INTERACTION);
    }

    /** Stock alerts only. */
    public function scopeStock(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_STOCK);
    }

    /** Reminder alerts only. */
    public function scopeReminder(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_REMINDER);
    }

    /** Danger severity only. */
    public function scopeDanger(Builder $query): Builder
    {
        return $query->where('severity', self::SEVERITY_DANGER);
    }

    /* ─── Helpers ────────────────────────────────────────────────────── */

    /**
     * Mark this alert as read.
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    /**
     * Returns the Tailwind CSS colour token matching the alert severity,
     * used by Blade components to render the correct badge / border colour.
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_DANGER  => '#E24B4A',
            self::SEVERITY_WARNING => '#EF9F27',
            default                => '#185FA5',   // info → primary blue
        };
    }

    /**
     * Returns a human-readable Indonesian label for the alert type.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_INTERACTION => 'Interaksi Obat',
            self::TYPE_STOCK       => 'Stok Menipis',
            self::TYPE_REMINDER    => 'Pengingat Jadwal',
            default                => ucfirst($this->type),
        };
    }

    /**
     * Returns a human-readable Indonesian label for the severity.
     */
    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_DANGER  => 'Bahaya',
            self::SEVERITY_WARNING => 'Peringatan',
            default                => 'Informasi',
        };
    }

    /* ─── Static Factory Helpers ─────────────────────────────────────── */

    /**
     * Quickly create a stock-low alert for a given user and medicine.
     */
    public static function createStockAlert(int $userId, Medicine $medicine): self
    {
        return self::create([
            'user_id'       => $userId,
            'type'          => self::TYPE_STOCK,
            'severity'      => self::SEVERITY_WARNING,
            'message'       => "Stok obat \"{$medicine->name}\" tinggal {$medicine->stock} {$medicine->unit}. Segera lakukan pengisian ulang.",
            'is_read'       => false,
            'alertable_type' => Medicine::class,
            'alertable_id'  => $medicine->id,
        ]);
    }

    /**
     * Quickly create an interaction alert between two medicines.
     */
    public static function createInteractionAlert(int $userId, Medicine $med1, Medicine $med2, string $detail = ''): self
    {
        $message = "Potensi interaksi obat terdeteksi antara \"{$med1->name}\" dan \"{$med2->name}\".";
        if ($detail) {
            $message .= " {$detail}";
        }

        return self::create([
            'user_id'  => $userId,
            'type'     => self::TYPE_INTERACTION,
            'severity' => self::SEVERITY_DANGER,
            'message'  => $message,
            'is_read'  => false,
        ]);
    }

    /**
     * Quickly create a missed-dose reminder alert.
     */
    public static function createReminderAlert(int $userId, MedicineSchedule $schedule): self
    {
        return self::create([
            'user_id'        => $userId,
            'type'           => self::TYPE_REMINDER,
            'severity'       => self::SEVERITY_INFO,
            'message'        => "Pengingat: jadwal konsumsi \"{$schedule->medicine->name}\" pukul {$schedule->schedule_time->format('H:i')} belum dicatat.",
            'is_read'        => false,
            'alertable_type' => MedicineSchedule::class,
            'alertable_id'   => $schedule->id,
        ]);
    }
}
