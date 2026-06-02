<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicineSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'medicine_schedules';

    protected $fillable = [
        'user_id',
        'family_member_id',
        'medicine_id',
        'schedule_time',
        'frequency',
        'dosage_amount',
        'notes',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /* ─── Relationships ─── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ScheduleLog::class, 'medicine_schedule_id');
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(Consumption::class, 'medicine_schedule_id');
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'daily' => 'Setiap Hari',
            'twice_daily' => '2x Sehari',
            'three_daily' => '3x Sehari',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'as_needed' => 'Bila Perlu',
            default => ucfirst($this->frequency),
        };
    }
}
