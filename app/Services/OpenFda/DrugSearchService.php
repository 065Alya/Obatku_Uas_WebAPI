<?php

namespace App\Services\OpenFda;

use App\Http\Clients\OpenFdaClient;
use App\Services\OpenFda\Transformers\DrugLabelTransformer;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * OpenFDA Drug Search Service
 *
 * Handles medicine search and generic name autofill.
 * All results are served from cache when available (72h TTL).
 */
final class DrugSearchService
{
    public function __construct(
        private readonly OpenFdaClient       $client,
        private readonly OpenFdaCacheService $cache,
        private readonly DrugLabelTransformer $transformer,
    ) {}

    /* ─────────────────────────────────────────────────────────────────────
     | Medicine Search
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Search medicines by name (brand or generic, case-insensitive fuzzy).
     *
     * @param  string $term   User search query
     * @param  int    $limit  Maximum results (capped at config max)
     * @return array{total: int, drugs: array}
     */
    public function search(string $term, int $limit = 10): array
    {
        $limit = min($limit, config('openfda.search.max_limit', 50));

        $cacheKey = $this->cache->searchKey($term, $limit);

        return $this->cache->remember($cacheKey, function () use ($term, $limit) {
            try {
                $raw = $this->client->fuzzySearch($term, $limit);
                return $this->transformer->transformSearchResults($raw);
            } catch (RuntimeException $e) {
                Log::warning('[DrugSearchService] Search failed', [
                    'term'  => $term,
                    'error' => $e->getMessage(),
                ]);

                // Return empty result rather than propagating to avoid breaking UI
                return ['total' => 0, 'drugs' => []];
            }
        });
    }

    /**
     * Search by exact brand name.
     */
    public function searchByBrand(string $brandName, int $limit = 10): array
    {
        $cacheKey = $this->cache->labelKey("brand:{$brandName}:{$limit}");

        return $this->cache->remember($cacheKey, function () use ($brandName, $limit) {
            $raw = $this->client->findByBrandName($brandName, $limit);
            return $this->transformer->transformSearchResults($raw);
        });
    }

    /**
     * Search by generic (INN) name.
     */
    public function searchByGenericName(string $genericName, int $limit = 10): array
    {
        $cacheKey = $this->cache->labelKey("generic_search:{$genericName}:{$limit}");

        return $this->cache->remember($cacheKey, function () use ($genericName, $limit) {
            $raw = $this->client->findByGenericName($genericName, $limit);
            return $this->transformer->transformSearchResults($raw);
        });
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Generic Name Autofill
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Resolve the generic (INN) name for a given brand name.
     * Returns null if the brand name cannot be resolved.
     */
    public function resolveGenericName(string $brandName): ?string
    {
        $cacheKey = $this->cache->genericNameKey($brandName);

        return $this->cache->remember($cacheKey, function () use ($brandName) {
            try {
                $raw = $this->client->findByBrandName($brandName, 3);
                return $this->transformer->toGenericName($raw);
            } catch (RuntimeException $e) {
                Log::warning('[DrugSearchService] Generic name resolution failed', [
                    'brand' => $brandName,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Get a list of generic name suggestions for autofill dropdowns.
     *
     * @return string[]
     */
    public function getGenericNameSuggestions(string $brandName, int $limit = 5): array
    {
        $cacheKey = $this->cache->genericNameKey("suggest:{$brandName}:{$limit}");

        return $this->cache->remember($cacheKey, function () use ($brandName, $limit) {
            try {
                $raw = $this->client->findByBrandName($brandName, $limit);
                return $this->transformer->toGenericNameSuggestions($raw);
            } catch (RuntimeException) {
                return [];
            }
        });
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Cache Management
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Bust the cache for a specific search term.
     */
    public function bustSearchCache(string $term, int $limit = 10): void
    {
        $this->cache->forget($this->cache->searchKey($term, $limit));
    }

    /**
     * Bust generic name cache for a brand.
     */
    public function bustGenericCache(string $brandName): void
    {
        $this->cache->forget($this->cache->genericNameKey($brandName));
    }
}
