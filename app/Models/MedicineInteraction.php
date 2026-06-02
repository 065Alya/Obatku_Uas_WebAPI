<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_a_id',
        'medicine_b_id',
        'severity',
        'description',
        'recommendation',
    ];

    /* ─── Relationships ─── */

    public function medicineA(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_a_id');
    }

    public function medicineB(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_b_id');
    }

    /* ─── Helpers ─── */

    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'mild' => 'Ringan',
            'moderate' => 'Sedang',
            'severe' => 'Berat',
            'contraindicated' => 'Kontraindikasi',
            default => $this->severity,
        };
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'mild' => 'warning',
            'moderate' => 'warning',
            'severe' => 'danger',
            'contraindicated' => 'danger',
            default => 'primary',
        };
    }
}
