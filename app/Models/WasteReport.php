<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'medicine_id',
        'medicine_name',
        'medicine_form',
        'quantity',
        'unit',
        'disposal_method',
        'notes',
        'status',
        'disposed_at',
    ];

    protected $casts = [
        'quantity'    => 'decimal:2',
        'disposed_at' => 'date',
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

    /* ─── Helpers ─── */

    public static function disposalMethodLabel(string $method): string
    {
        return match ($method) {
            'pharmacy_return'  => 'Kembalikan ke Apotek',
            'household_trash'  => 'Sampah Rumah Tangga (dirusak dulu)',
            'collection_point' => 'Titik Pengumpulan Obat',
            'flush'            => 'Siram ke Toilet (hanya jika diizinkan)',
            'bury'             => 'Penguburan (obat non-cair)',
            default            => ucfirst(str_replace('_', ' ', $method)),
        };
    }

    public function getDisposalMethodLabelAttribute(): string
    {
        return self::disposalMethodLabel($this->disposal_method);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'verified' => '#1D9E75',
            'rejected' => '#E24B4A',
            default    => '#EF9F27',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            default    => 'Menunggu Verifikasi',
        };
    }
}
