<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonalProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'gender',
        'birth_date',
        'phone',
        'address',
        // Extended health fields
        'blood_type',
        'height_cm',
        'weight_kg',
        'allergies',
        'chronic_diseases',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'height_cm'  => 'decimal:1',
        'weight_kg'  => 'decimal:1',
    ];

    /* ─── Relationship ─── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ─── Computed Attributes ─── */

    /**
     * Body Mass Index (kg / m²), rounded to 1 decimal.
     */
    public function getBmiAttribute(): ?float
    {
        if (!$this->height_cm || !$this->weight_kg || $this->height_cm <= 0) {
            return null;
        }
        $heightM = $this->height_cm / 100;
        return round($this->weight_kg / ($heightM * $heightM), 1);
    }

    /**
     * WHO BMI classification (Indonesian labels).
     */
    public function getBmiCategoryAttribute(): string
    {
        $bmi = $this->bmi;
        if ($bmi === null) return '–';

        return match (true) {
            $bmi < 18.5 => 'Berat Badan Kurang',
            $bmi < 25.0 => 'Normal',
            $bmi < 30.0 => 'Berat Badan Lebih',
            default     => 'Obesitas',
        };
    }

    /**
     * Age derived from birth_date.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    /**
     * Localised gender label.
     */
    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'male'   => 'Laki-laki',
            'female' => 'Perempuan',
            'other'  => 'Lainnya',
            default  => '–',
        };
    }

    /* ─── Scopes ─── */

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
