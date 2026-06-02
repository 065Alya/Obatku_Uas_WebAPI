<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_schedule_id',
        'status',
        'taken_at',
        'skipped_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'datetime',
        ];
    }

    /* ─── Relationships ─── */

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MedicineSchedule::class, 'medicine_schedule_id');
    }

    /* ─── Helpers ─── */

    public function isTaken(): bool
    {
        return $this->status === 'taken';
    }

    public function isSkipped(): bool
    {
        return $this->status === 'skipped';
    }

    public function isMissed(): bool
    {
        return $this->status === 'missed';
    }
}
