<?php

namespace App\Services\OpenFda\Transformers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * DrugLabelTransformer
 *
 * Converts raw OpenFDA drug/label API payloads into clean,
 * application-ready data structures suitable for serialisation.
 */
final class DrugLabelTransformer
{
    /* ─────────────────────────────────────────────────────────────────────
     | Search Results — list of drug summaries
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Transform a full search response into a paginated summary array.
     *
     * @param  array $raw  Raw decoded JSON from OpenFDA
     * @return array{total: int, drugs: array}
     */
    public function transformSearchResults(array $raw): array
    {
        $total   = Arr::get($raw, 'meta.results.total', 0);
        $results = Arr::get($raw, 'results', []);

        return [
            'total' => $total,
            'drugs' => array_map([$this, 'toSummary'], $results),
        ];
    }

    /**
     * Produce a compact summary DTO for a single label result.
     */
    public function toSummary(array $label): array
    {
        $openfda = $label['openfda'] ?? [];

        return [
            'brand_name'      => $this->first($openfda, 'brand_name'),
            'generic_name'    => $this->first($openfda, 'generic_name'),
            'manufacturer'    => $this->first($openfda, 'manufacturer_name'),
            'product_ndc'     => $this->first($openfda, 'product_ndc'),
            'application_number' => $this->first($openfda, 'application_number'),
            'route'           => $this->first($openfda, 'route'),
            'dosage_form'     => $this->first($openfda, 'dosage_form'),
            'product_type'    => $this->first($openfda, 'product_type'),
            'substance_name'  => Arr::get($openfda, 'substance_name', []),
        ];
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Full Literacy Card — detailed drug information
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Build a medicine literacy card from the first label result.
     *
     * @param  array $raw  Raw decoded JSON from OpenFDA
     * @return array|null  null when no results found
     */
    public function toLiteracyCard(array $raw): ?array
    {
        $result = Arr::get($raw, 'results.0');
        if (!$result) return null;

        $openfda = $result['openfda'] ?? [];

        return [
            // Identity
            'brand_name'      => $this->first($openfda, 'brand_name'),
            'generic_name'    => $this->first($openfda, 'generic_name'),
            'manufacturer'    => $this->first($openfda, 'manufacturer_name'),
            'product_ndc'     => $this->first($openfda, 'product_ndc'),
            'application_number' => $this->first($openfda, 'application_number'),

            // Classification
            'route'           => $this->first($openfda, 'route'),
            'dosage_form'     => $this->first($openfda, 'dosage_form'),
            'product_type'    => $this->first($openfda, 'product_type'),
            'pharm_class'     => Arr::get($openfda, 'pharm_class_epc', []),
            'substance_name'  => Arr::get($openfda, 'substance_name', []),

            // Clinical information (may be null if not on label)
            'indications_and_usage'  => $this->firstText($result, 'indications_and_usage'),
            'contraindications'      => $this->firstText($result, 'contraindications'),
            'warnings'               => $this->firstText($result, 'warnings'),
            'dosage_and_administration' => $this->firstText($result, 'dosage_and_administration'),
            'adverse_reactions'      => $this->firstText($result, 'adverse_reactions'),
            'drug_interactions'      => $this->firstText($result, 'drug_interactions'),
            'overdosage'             => $this->firstText($result, 'overdosage'),
            'how_supplied'           => $this->firstText($result, 'how_supplied'),
            'storage_and_handling'   => $this->firstText($result, 'storage_and_handling'),
            'mechanism_of_action'    => $this->firstText($result, 'mechanism_of_action'),

            // Metadata
            'set_id'          => $result['set_id'] ?? null,
            'effective_time'  => $result['effective_time'] ?? null,
            'version'         => $result['version'] ?? null,
        ];
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Generic Name Autofill
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Extract the generic name from the first matching label result.
     */
    public function toGenericName(array $raw): ?string
    {
        $results = Arr::get($raw, 'results', []);
        if (empty($results)) return null;

        foreach ($results as $result) {
            $generic = $this->first($result['openfda'] ?? [], 'generic_name');
            if ($generic) return $generic;
        }

        return null;
    }

    /**
     * Produce a list of unique generic name suggestions for autofill.
     *
     * @return string[]
     */
    public function toGenericNameSuggestions(array $raw): array
    {
        $results = Arr::get($raw, 'results', []);

        return collect($results)
            ->map(fn($r) => $this->first($r['openfda'] ?? [], 'generic_name'))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Helpers
     |──────────────────────────────────────────────────────────────────── */

    /** Return the first element of an OpenFDA array field, or null. */
    private function first(array $data, string $key): ?string
    {
        $value = Arr::get($data, $key);

        if (is_array($value)) {
            return Str::title($value[0] ?? '') ?: null;
        }

        return is_string($value) ? Str::title($value) : null;
    }

    /** Return the first string from a label text field array. */
    private function firstText(array $result, string $key): ?string
    {
        $value = $result[$key] ?? null;

        if (is_array($value)) {
            return trim($value[0] ?? '') ?: null;
        }

        return is_string($value) ? trim($value) : null;
    }
}
