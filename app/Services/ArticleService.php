<?php

namespace App\Services;

use App\Models\HealthArticle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ArticleService
{
    /**
     * Get published articles (paginated).
     */
    public function getPublished(int $perPage = 12): LengthAwarePaginator
    {
        return HealthArticle::published()
            ->with('author')
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    /**
     * Get all articles for admin.
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return HealthArticle::with('author')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Find by slug (public view).
     */
    public function findBySlug(string $slug): ?HealthArticle
    {
        $article = HealthArticle::where('slug', $slug)->published()->first();

        if ($article) {
            $article->increment('views_count');
        }

        return $article;
    }

    /**
     * Create article.
     */
    public function create(array $data): Model
    {
        $data['slug'] = Str::slug($data['title']);
        $data['author_id'] = $data['author_id'] ?? auth()->id();

        return HealthArticle::create($data);
    }

    /**
     * Update article.
     */
    public function update(int $id, array $data): Model
    {
        $article = HealthArticle::findOrFail($id);

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $article->update($data);
        return $article->fresh();
    }

    /**
     * Delete article.
     */
    public function delete(int $id): bool
    {
        return HealthArticle::findOrFail($id)->delete();
    }

    /**
     * Get featured/popular articles.
     */
    public function getPopular(int $limit = 5): Collection
    {
        return HealthArticle::published()
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get();
    }
}
