<?php

namespace App\Services\OpenFda\Transformers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * InteractionTransformer
 *
 * Converts raw OpenFDA adverse event data into structured
 * interaction signals, scoring each by severity.
 */
final class InteractionTransformer
{
    private array $severityKeywords;

    public function __construct()
    {
        $this->severityKeywords = config('openfda.interaction.severity_keywords', []);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Public API
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Transform a set of adverse event results into interaction signals.
     *
     * @param  array  $raw        Raw OpenFDA drug/event response
     * @param  string $drugName   The drug being analysed
     * @param  array  $coDrugs    Other drugs to check co-occurrence with
     * @return array{
     *   drug: string,
     *   interaction_count: int,
     *   severity: string,
     *   signals: array,
     *   co_occurring_drugs: array
     * }
     */
    public function transform(array $raw, string $drugName, array $coDrugs = []): array
    {
        $results = Arr::get($raw, 'results', []);
        $total   = Arr::get($raw, 'meta.results.total', 0);

        if (empty($results)) {
            return $this->emptyResponse($drugName);
        }

        $signals      = $this->extractSignals($results);
        $coDrugHits   = $this->findCoDrugOccurrences($results, $coDrugs);
        $severity     = $this->computeOverallSeverity($signals);

        return [
            'drug'               => Str::title($drugName),
            'total_reports'      => $total,
            'interaction_count'  => count($signals),
            'severity'           => $severity,
            'signals'            => $signals,
            'co_occurring_drugs' => $coDrugHits,
        ];
    }

    /**
     * Build a combined interaction report for two or more drugs.
     *
     * @param  array  $rawMap  ['drugName' => rawApiResponse, ...]
     * @return array
     */
    public function transformMulti(array $rawMap): array
    {
        $reports   = [];
        $allDrugs  = array_keys($rawMap);

        foreach ($rawMap as $drugName => $raw) {
            $others    = array_filter($allDrugs, fn($d) => $d !== $drugName);
            $reports[] = $this->transform($raw, $drugName, array_values($others));
        }

        $overallSeverity = $this->computeHighestSeverity(
            array_column($reports, 'severity')
        );

        return [
            'drugs'            => $allDrugs,
            'overall_severity' => $overallSeverity,
            'reports'          => $reports,
            'checked_at'       => now()->toIso8601String(),
        ];
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Private — Signal Extraction
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Extract interaction signals from a list of adverse event reports.
     */
    private function extractSignals(array $results): array
    {
        $signals = [];

        foreach ($results as $event) {
            $reactions = collect(Arr::get($event, 'patient.reaction', []))
                ->pluck('reactionmeddrapt')
                ->filter()
                ->toArray();

            if (empty($reactions)) continue;

            $narrative = $this->buildNarrative($event);
            $severity  = $this->detectSeverity($narrative);

            $signals[] = [
                'reactions'     => $reactions,
                'severity'      => $severity,
                'serious'       => (bool) Arr::get($event, 'serious', false),
                'serious_death' => (bool) Arr::get($event, 'seriousnessdeath', false),
                'report_date'   => Arr::get($event, 'receivedate'),
                'outcome'       => Arr::get($event, 'patient.patientdeath.patientdeathdateformat'),
            ];
        }

        return $signals;
    }

    /**
     * Find which co-drugs appear together with the primary drug in events.
     */
    private function findCoDrugOccurrences(array $results, array $coDrugs): array
    {
        if (empty($coDrugs)) return [];

        $hits = [];

        foreach ($results as $event) {
            $drugs = collect(Arr::get($event, 'patient.drug', []))
                ->pluck('medicinalproduct')
                ->map(fn($d) => Str::lower($d ?? ''))
                ->filter()
                ->toArray();

            foreach ($coDrugs as $coDrug) {
                $coDrugLower = Str::lower($coDrug);
                $matched = array_filter($drugs, fn($d) => str_contains($d, $coDrugLower));

                if (!empty($matched)) {
                    $hits[$coDrug] = ($hits[$coDrug] ?? 0) + 1;
                }
            }
        }

        arsort($hits);

        return array_map(fn($drug, $count) => [
            'drug'  => Str::title($drug),
            'count' => $count,
        ], array_keys($hits), array_values($hits));
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Private — Severity Scoring
     |──────────────────────────────────────────────────────────────────── */

    private function buildNarrative(array $event): string
    {
        $reactions = collect(Arr::get($event, 'patient.reaction', []))
            ->pluck('reactionmeddrapt')
            ->implode(' ');

        return strtolower($reactions);
    }

    private function detectSeverity(string $text): string
    {
        foreach (['fatal', 'serious', 'moderate', 'mild'] as $level) {
            $keywords = $this->severityKeywords[$level] ?? [];
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) return $level;
            }
        }

        // Use seriousness flag as fallback
        return 'unknown';
    }

    private function computeOverallSeverity(array $signals): string
    {
        $severities = array_column($signals, 'severity');
        return $this->computeHighestSeverity($severities);
    }

    private function computeHighestSeverity(array $severities): string
    {
        $order = ['fatal' => 4, 'serious' => 3, 'moderate' => 2, 'mild' => 1, 'unknown' => 0];

        $highest = 'unknown';
        foreach ($severities as $sev) {
            if (($order[$sev] ?? -1) > ($order[$highest] ?? -1)) {
                $highest = $sev;
            }
        }

        return $highest;
    }

    private function emptyResponse(string $drugName): array
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
}
