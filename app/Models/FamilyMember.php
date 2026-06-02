<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'family_id',
        'name',
        'relationship',
        'birth_date',
        'health_notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    /* ─── Relationships ─── */

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function medicines(): MorphMany
    {
        return $this->morphMany(Medicine::class, 'owner');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(MedicineSchedule::class);
    }

    /* ─── Helpers ─── */

    public function getAgeAttribute(): int
    {
        return $this->birth_date ? $this->birth_date->age : 0;
    }
}
