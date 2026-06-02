<?php

namespace App\Services\OpenFda;

use App\Http\Clients\OpenFdaClient;
use App\Services\OpenFda\Transformers\DrugLabelTransformer;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Drug Literacy Card Service
 *
 * Builds comprehensive, patient-friendly medicine information cards
 * from OpenFDA drug label data — indications, warnings, dosage,
 * interactions, adverse reactions, and storage instructions.
 */
final class DrugLiteracyService
{
    public function __construct(
        private readonly OpenFdaClient        $client,
        private readonly OpenFdaCacheService  $cache,
        private readonly DrugLabelTransformer $transformer,
    ) {}

    /* ─────────────────────────────────────────────────────────────────────
     | Literacy Card
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Build a medicine literacy card by brand name.
     *
     * @param  string $brandName  Drug brand name
     * @return array|null  null if drug not found in OpenFDA
     */
    public function getCardByBrandName(string $brandName): ?array
    {
        $cacheKey = $this->cache->literacyKey("brand:{$brandName}");

        return $this->cache->remember($cacheKey, function () use ($brandName) {
            try {
                $raw = $this->client->findByBrandName($brandName, 1);
                return $this->transformer->toLiteracyCard($raw);
            } catch (RuntimeException $e) {
                Log::warning('[DrugLiteracyService] Brand name card failed', [
                    'brand' => $brandName,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Build a medicine literacy card by generic (INN) name.
     */
    public function getCardByGenericName(string $genericName): ?array
    {
        $cacheKey = $this->cache->literacyKey("generic:{$genericName}");

        return $this->cache->remember($cacheKey, function () use ($genericName) {
            try {
                $raw = $this->client->findByGenericName($genericName, 1);
                return $this->transformer->toLiteracyCard($raw);
            } catch (RuntimeException $e) {
                Log::warning('[DrugLiteracyService] Generic name card failed', [
                    'generic' => $genericName,
                    'error'   => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Attempt to resolve a card by brand name, falling back to generic name.
     * Useful when the user has entered a name that might be either.
     */
    public function getCard(string $name): ?array
    {
        // Try brand name first
        $card = $this->getCardByBrandName($name);

        if ($card !== null) {
            return $card;
        }

        // Fall back to generic name search
        return $this->getCardByGenericName($name);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Enriched Card — with interaction context
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Return the literacy card enriched with a pre-built interaction
     * summary for the given drug name. This powers the full detail view.
     *
     * @param  string $name       Brand or generic name
     * @param  array  $coDrugs    Optional: other drugs user is taking
     */
    public function getEnrichedCard(string $name, array $coDrugs = []): array
    {
        $card = $this->getCard($name);

        return [
            'found'           => $card !== null,
            'card'            => $card,
            'name_searched'   => $name,
            'co_drugs_checked' => $coDrugs,
            'retrieved_at'    => now()->toIso8601String(),
        ];
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Cache Management
     |──────────────────────────────────────────────────────────────────── */

    public function bustCache(string $name): void
    {
        $this->cache->forget($this->cache->literacyKey("brand:{$name}"));
        $this->cache->forget($this->cache->literacyKey("generic:{$name}"));
    }
}
