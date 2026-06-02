<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Family extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'family_name',
        'description',
    ];

    /**
     * Get the user that owns the family.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
