<?php

namespace App\Http\Clients;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * OpenFDA HTTP Client
 *
 * Thin, stateless HTTP wrapper around the OpenFDA REST API.
 * Handles authentication, retry logic, and error normalisation.
 * All response caching is delegated to the service layer.
 */
final class OpenFdaClient
{
    private string $baseUrl;
    private string $apiKey;
    private int    $timeout;
    private int    $connectTimeout;
    private int    $retryTimes;
    private int    $retrySleep;

    public function __construct()
    {
        $cfg = config('openfda');

        $this->baseUrl        = rtrim($cfg['base_url'], '/');
        $this->apiKey         = $cfg['api_key'] ?? '';
        $this->timeout        = $cfg['http']['timeout'];
        $this->connectTimeout = $cfg['http']['connect_timeout'];
        $this->retryTimes     = $cfg['http']['retry']['times'];
        $this->retrySleep     = $cfg['http']['retry']['sleep'];
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Public API
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Search the drug/label endpoint.
     *
     * @param  string $search  Raw OpenFDA search expression
     * @param  int    $limit   Results per page (max 1000 per OpenFDA docs)
     * @param  int    $skip    Offset for pagination
     * @return array{meta: array, results: array}
     *
     * @throws RuntimeException on non-recoverable errors
     */
    public function searchLabel(string $search, int $limit = 10, int $skip = 0): array
    {
        return $this->get(config('openfda.endpoints.drug_label'), [
            'search' => $search,
            'limit'  => $limit,
            'skip'   => $skip,
        ]);
    }

    /**
     * Search the drug/event (adverse events) endpoint.
     */
    public function searchEvent(string $search, int $limit = 10, int $skip = 0): array
    {
        return $this->get(config('openfda.endpoints.drug_event'), [
            'search' => $search,
            'limit'  => $limit,
            'skip'   => $skip,
        ]);
    }

    /**
     * Search the drug/ndc (national drug code) endpoint.
     */
    public function searchNdc(string $search, int $limit = 10): array
    {
        return $this->get(config('openfda.endpoints.drug_ndc'), [
            'search' => $search,
            'limit'  => $limit,
        ]);
    }

    /**
     * Fetch a single drug label by exact brand name.
     */
    public function findByBrandName(string $brandName, int $limit = 5): array
    {
        return $this->searchLabel(
            sprintf('openfda.brand_name:"%s"', addslashes($brandName)),
            $limit
        );
    }

    /**
     * Fetch drug labels by generic (INN) name.
     */
    public function findByGenericName(string $genericName, int $limit = 5): array
    {
        return $this->searchLabel(
            sprintf('openfda.generic_name:"%s"', addslashes($genericName)),
            $limit
        );
    }

    /**
     * Fuzzy search across brand + generic name simultaneously.
     */
    public function fuzzySearch(string $term, int $limit = 10): array
    {
        $escaped = addslashes($term);

        return $this->searchLabel(
            sprintf(
                'openfda.brand_name:"%s"+openfda.generic_name:"%s"',
                $escaped,
                $escaped
            ),
            $limit
        );
    }

    /**
     * Fetch drug adverse events to surface interaction signals.
     */
    public function getAdverseEvents(string $drugName, int $limit = 20): array
    {
        return $this->searchEvent(
            sprintf('patient.drug.medicinalproduct:"%s"', addslashes($drugName)),
            $limit
        );
    }

    /* ─────────────────────────────────────────────────────────────────────
     | HTTP Core
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Execute a GET request against the OpenFDA API.
     *
     * @throws RuntimeException
     */
    private function get(string $path, array $query = []): array
    {
        if ($this->apiKey !== '') {
            $query['api_key'] = $this->apiKey;
        }

        try {
            /** @var Response $response */
            $response = Http::timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->retry($this->retryTimes, $this->retrySleep, function (\Exception $e, Response $response) {
                    // Only retry on 429 (rate limit) or 5xx server errors
                    if ($response instanceof Response) {
                        return $response->status() === 429 || $response->serverError();
                    }

                    return $e instanceof ConnectionException;
                })
                ->get($this->baseUrl . $path, $query);

            if ($response->status() === 404) {
                // 404 from OpenFDA means zero results — return empty payload
                return ['meta' => [], 'results' => []];
            }

            $response->throw(); // throws RequestException for other 4xx/5xx

            return $response->json();

        } catch (RequestException $e) {
            $status  = $e->response->status();
            $message = $e->response->json('error.message', 'Unknown OpenFDA error');

            Log::warning('[OpenFDA] API error', [
                'status'  => $status,
                'message' => $message,
                'path'    => $path,
            ]);

            throw new RuntimeException("[OpenFDA] {$status}: {$message}", $status, $e);

        } catch (ConnectionException $e) {
            Log::error('[OpenFDA] Connection error', ['path' => $path, 'error' => $e->getMessage()]);
            throw new RuntimeException('[OpenFDA] Service unreachable. Please try again later.', 503, $e);
        }
    }
}
