<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_type',
        'owner_id',
        'category_id',
        'medicine_name',
        'generic_name',
        'dosage',
        'unit',
        'form',
        'manufacturer',
        'description',
        'side_effects',
        'stock',
        'stock_alert',
        'price',
        'expiry_date',
        'image',
        'is_active',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'stock_alert' => 'integer',
    ];

    /* ─── Relationships ─── */

    /**
     * Get the owner of the medicine (User or FamilyMember).
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(MedicineSchedule::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(MedicineInteraction::class, 'medicine_a_id');
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(Consumption::class);
    }

    public function expiryNotificationLogs(): HasMany
    {
        return $this->hasMany(ExpiryNotificationLog::class);
    }

    /* ─── Helpers ─── */

    public function getNameAttribute(): string
    {
        return $this->medicine_name;
    }

    public function getStockAlertThresholdAttribute(): int
    {
        return $this->stock_alert;
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->stock_alert;
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && $this->expiry_date->lte(now()->addDays($days));
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->lt(now());
    }
}
