<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisposalGuide extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_form',
        'title',
        'description',
        'steps',
        'icon',
        'color',
        'is_active',
    ];

    protected $casts = [
        'steps'     => 'array',
        'is_active' => 'boolean',
    ];

    /* ─── Helpers ─── */

    /**
     * Returns the matching guide for a given medicine form.
     */
    public static function forForm(string $form): ?self
    {
        return self::where('medicine_form', $form)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Returns all active guides keyed by medicine form.
     */
    public static function allActive(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('is_active', true)->get();
    }
}
