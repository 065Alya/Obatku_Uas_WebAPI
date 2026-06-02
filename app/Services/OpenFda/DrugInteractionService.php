<?php

namespace App\Services\OpenFda;

use App\Http\Clients\OpenFdaClient;
use App\Services\OpenFda\Transformers\InteractionTransformer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Drug Interaction Service
 *
 * Detects potential interactions between one or more drugs by querying
 * the OpenFDA adverse events endpoint and scoring co-occurrence signals.
 */
final class DrugInteractionService
{
    public function __construct(
        private readonly OpenFdaClient          $client,
        private readonly OpenFdaCacheService    $cache,
        private readonly InteractionTransformer $transformer,
    ) {}

    /* ─────────────────────────────────────────────────────────────────────
     | Single Drug — Adverse Event Signals
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Analyse adverse events for a single drug.
     *
     * @param  string $drugName
     * @return array
     */
    public function analyseForDrug(string $drugName): array
    {
        $cacheKey = $this->cache->adverseEventsKey($drugName);

        return $this->cache->remember($cacheKey, function () use ($drugName) {
            try {
                $limit = config('openfda.search.interaction_limit', 20);
                $raw   = $this->client->getAdverseEvents($drugName, $limit);
                return $this->transformer->transform($raw, $drugName);
            } catch (RuntimeException $e) {
                Log::warning('[DrugInteractionService] Single drug analysis failed', [
                    'drug'  => $drugName,
                    'error' => $e->getMessage(),
                ]);
                return $this->emptyResult($drugName);
            }
        });
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Multi-Drug — Co-occurrence Interaction Check
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Check interactions across a list of drugs.
     * Fetches adverse event data for each and looks for co-occurrences.
     *
     * @param  string[] $drugNames  At least 2 drug names required
     * @return array
     */
    public function checkInteractions(array $drugNames): array
    {
        if (count($drugNames) < 1) {
            return ['error' => 'At least one drug name is required.'];
        }

        $cacheKey = $this->cache->interactionKey(...$drugNames);

        return $this->cache->remember($cacheKey, function () use ($drugNames) {
            $rawMap = [];
            $limit  = config('openfda.search.interaction_limit', 20);

            foreach ($drugNames as $name) {
                try {
                    $rawMap[$name] = $this->client->getAdverseEvents($name, $limit);
                } catch (RuntimeException $e) {
                    Log::warning('[DrugInteractionService] Failed to fetch events for drug', [
                        'drug'  => $name,
                        'error' => $e->getMessage(),
                    ]);
                    $rawMap[$name] = ['meta' => [], 'results' => []];
                }
            }

            return $this->transformer->transformMulti($rawMap);
        });
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Quick Interaction Flag (for inline UI)
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Returns a simplified flag indicating whether a potential interaction
     * exists between two drugs, suitable for inline UI warnings.
     *
     * @return array{has_interaction: bool, severity: string, message: string}
     */
    public function quickCheck(string $drugA, string $drugB): array
    {
        $result   = $this->checkInteractions([$drugA, $drugB]);
        $severity = $result['overall_severity'] ?? 'none';

        $hasInteraction = !in_array($severity, ['none', 'unknown'], true);

        return [
            'has_interaction' => $hasInteraction,
            'severity'        => $severity,
            'message'         => $this->buildQuickMessage($hasInteraction, $severity, $drugA, $drugB),
        ];
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Cache Management
     |──────────────────────────────────────────────────────────────────── */

    public function bustInteractionCache(string ...$drugNames): void
    {
        $this->cache->forget($this->cache->interactionKey(...$drugNames));
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Private Helpers
     |──────────────────────────────────────────────────────────────────── */

    private function emptyResult(string $drugName): array
    {
        return [
            'drug'               => Str::title($drugName),
            'total_reports'      => 0,
            'interaction_count'  => 0,
            'severity'           => 'none',
            'signals'            => [],
            'co_occurring_drugs' => [],
        ];
    }

    private function buildQuickMessage(bool $has, string $severity, string $drugA, string $drugB): string
    {
        if (!$has) {
            return "No significant interaction signals found between {$drugA} and {$drugB}.";
        }

        return match ($severity) {
            'fatal'    => "⛔ FATAL: Potentially life-threatening interaction detected between {$drugA} and {$drugB}. Consult a physician immediately.",
            'serious'  => "🚨 SERIOUS: A serious interaction signal exists between {$drugA} and {$drugB}. Seek medical advice before use.",
            'moderate' => "⚠️ MODERATE: Exercise caution when using {$drugA} with {$drugB}. Monitor closely.",
            default    => "ℹ️ MILD: A minor interaction signal found between {$drugA} and {$drugB}.",
        };
    }
}
