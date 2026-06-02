<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'image',
        'category',
        'tags',
        'is_published',
        'published_at',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    /* ─── Relationships ─── */

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /* ─── Scopes ─── */

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }
}
