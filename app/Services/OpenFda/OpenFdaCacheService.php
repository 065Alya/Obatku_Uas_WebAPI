<?php

namespace App\Services\OpenFda;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * OpenFDA Cache Layer
 *
 * Centralises all cache read/write operations for OpenFDA data.
 * Cache TTL defaults to 72 hours (config: openfda.cache.ttl).
 * All keys are namespaced under "openfda:" to avoid collisions.
 */
final class OpenFdaCacheService
{
    private int    $ttl;
    private string $prefix;
    private ?string $store;

    public function __construct()
    {
        $cfg         = config('openfda.cache');
        $this->ttl   = (int) $cfg['ttl'];
        $this->prefix = $cfg['prefix'];
        $this->store  = $cfg['store'] ?: null;
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Public API
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Remember a closure result for the configured TTL.
     * If a custom TTL is needed pass it as the third argument.
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return $this->store()->remember(
            $this->buildKey($key),
            $ttl ?? $this->ttl,
            $callback
        );
    }

    /**
     * Store a value manually.
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->store()->put(
            $this->buildKey($key),
            $value,
            $ttl ?? $this->ttl
        );
    }

    /**
     * Retrieve a cached value (returns null on miss).
     */
    public function get(string $key): mixed
    {
        return $this->store()->get($this->buildKey($key));
    }

    /**
     * Forget a specific cache entry.
     */
    public function forget(string $key): bool
    {
        return $this->store()->forget($this->buildKey($key));
    }

    /**
     * Check whether a key exists and is not expired.
     */
    public function has(string $key): bool
    {
        return $this->store()->has($this->buildKey($key));
    }

    /**
     * Flush all OpenFDA cache entries.
     * Only works if the cache store supports tagging.
     * Falls back to a no-op for stores that don't (e.g., database).
     */
    public function flush(): void
    {
        try {
            Cache::tags([$this->prefix])->flush();
        } catch (\BadMethodCallException) {
            // Tag-unsupported stores (database, file): silently skip
        }
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Key Builders — deterministic, collision-free
     |──────────────────────────────────────────────────────────────────── */

    public function searchKey(string $term, int $limit): string
    {
        return $this->buildKey("search:{$this->hash($term)}:{$limit}");
    }

    public function labelKey(string $drugName): string
    {
        return $this->buildKey("label:{$this->hash($drugName)}");
    }

    public function genericNameKey(string $brandName): string
    {
        return $this->buildKey("generic:{$this->hash($brandName)}");
    }

    public function interactionKey(string ...$drugNames): string
    {
        $sorted = collect($drugNames)->map(fn($n) => Str::lower(trim($n)))->sort()->implode('|');
        return $this->buildKey("interaction:{$this->hash($sorted)}");
    }

    public function literacyKey(string $drugName): string
    {
        return $this->buildKey("literacy:{$this->hash($drugName)}");
    }

    public function adverseEventsKey(string $drugName): string
    {
        return $this->buildKey("adverse:{$this->hash($drugName)}");
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Private Helpers
     |──────────────────────────────────────────────────────────────────── */

    private function buildKey(string $suffix): string
    {
        return "{$this->prefix}:{$suffix}";
    }

    private function hash(string $value): string
    {
        return md5(Str::lower(trim($value)));
    }

    private function store(): \Illuminate\Contracts\Cache\Repository
    {
        return $this->store ? Cache::store($this->store) : Cache::store();
    }
}
