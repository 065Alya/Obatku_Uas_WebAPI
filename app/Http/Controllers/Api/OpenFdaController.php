<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpenFda\DrugSearchRequest;
use App\Http\Requests\OpenFda\GenericNameRequest;
use App\Http\Requests\OpenFda\InteractionCheckRequest;
use App\Http\Requests\OpenFda\LiteracyCardRequest;
use App\Services\OpenFda\DrugInteractionService;
use App\Services\OpenFda\DrugLiteracyService;
use App\Services\OpenFda\DrugSearchService;
use App\Services\OpenFda\OpenFdaCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * OpenFDA API Proxy Controller
 *
 * Acts as the application-side proxy for all OpenFDA data requests.
 * Keeps the OpenFDA API key server-side, adds caching, validation,
 * and a consistent JSON envelope for all responses.
 *
 * Base prefix: /api/openfda
 */
class OpenFdaController extends Controller
{
    public function __construct(
        private readonly DrugSearchService      $searchService,
        private readonly DrugInteractionService $interactionService,
        private readonly DrugLiteracyService    $literacyService,
        private readonly OpenFdaCacheService    $cache,
    ) {}

    /* ─────────────────────────────────────────────────────────────────────
     | GET /api/openfda/search
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Search medicines by name (brand or generic).
     *
     * Query params:
     *   - q      (required) Search term
     *   - limit  (optional) 1–50, default 10
     *   - type   (optional) any|brand|generic
     */
    public function search(DrugSearchRequest $request): JsonResponse
    {
        try {
            $term  = $request->input('q');
            $limit = $request->limit();
            $type  = $request->searchType();

            $result = match ($type) {
                'brand'   => $this->searchService->searchByBrand($term, $limit),
                'generic' => $this->searchService->searchByGenericName($term, $limit),
                default   => $this->searchService->search($term, $limit),
            };

            return $this->success($result, 'Search completed successfully.');

        } catch (RuntimeException $e) {
            return $this->serviceError($e);
        }
    }

    /* ─────────────────────────────────────────────────────────────────────
     | GET /api/openfda/generic-name
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Resolve and autofill the generic (INN) name for a given brand name.
     *
     * Query params:
     *   - brand_name (required)
     *   - limit      (optional) Max suggestions returned
     */
    public function genericName(GenericNameRequest $request): JsonResponse
    {
        try {
            $brandName = $request->input('brand_name');

            $genericName  = $this->searchService->resolveGenericName($brandName);
            $suggestions  = $this->searchService->getGenericNameSuggestions(
                $brandName,
                (int) $request->input('limit', 5)
            );

            return $this->success([
                'brand_name'   => $brandName,
                'generic_name' => $genericName,
                'suggestions'  => $suggestions,
            ], 'Generic name resolved.');

        } catch (RuntimeException $e) {
            return $this->serviceError($e);
        }
    }

    /* ─────────────────────────────────────────────────────────────────────
     | POST /api/openfda/interactions
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Detect potential drug interactions.
     *
     * Body (JSON):
     *   - drugs (required) array of 1–5 drug names
     */
    public function checkInteractions(InteractionCheckRequest $request): JsonResponse
    {
        try {
            $drugs  = $request->drugNames();
            $result = $this->interactionService->checkInteractions($drugs);

            return $this->success($result, 'Interaction analysis complete.');

        } catch (RuntimeException $e) {
            return $this->serviceError($e);
        }
    }

    /* ─────────────────────────────────────────────────────────────────────
     | GET /api/openfda/interactions/quick
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Quick two-drug interaction flag for inline UI warnings.
     *
     * Query params:
     *   - drug_a (required)
     *   - drug_b (required)
     */
    public function quickInteraction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'drug_a' => ['required', 'string', 'min:2', 'max:100'],
            'drug_b' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        try {
            $result = $this->interactionService->quickCheck(
                $validated['drug_a'],
                $validated['drug_b']
            );

            return $this->success($result, 'Quick interaction check complete.');

        } catch (RuntimeException $e) {
            return $this->serviceError($e);
        }
    }

    /* ─────────────────────────────────────────────────────────────────────
     | GET /api/openfda/literacy-card
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Retrieve a full medicine literacy card.
     *
     * Query params:
     *   - name       (required) Brand or generic name
     *   - co_drugs[] (optional) Concurrent medications for interaction context
     */
    public function literacyCard(LiteracyCardRequest $request): JsonResponse
    {
        try {
            $name    = $request->input('name');
            $coDrugs = $request->coDrugs();

            $result  = $this->literacyService->getEnrichedCard($name, $coDrugs);

            $statusCode = $result['found'] ? 200 : 404;
            $message    = $result['found']
                ? 'Literacy card retrieved successfully.'
                : 'No drug information found for the provided name.';

            return $this->success($result, $message, $statusCode);

        } catch (RuntimeException $e) {
            return $this->serviceError($e);
        }
    }

    /* ─────────────────────────────────────────────────────────────────────
     | GET /api/openfda/cache/status
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Check whether a specific cache key is populated.
     * Useful for debugging and admin tooling.
     *
     * Query params:
     *   - key (required) Cache key suffix to check
     */
    public function cacheStatus(Request $request): JsonResponse
    {
        $request->validate([
            'key' => ['required', 'string', 'max:200'],
        ]);

        $key   = $request->input('key');
        $exists = $this->cache->has($key);

        return $this->success([
            'key'    => $key,
            'cached' => $exists,
        ], $exists ? 'Cache entry found.' : 'Cache entry not found.');
    }

    /* ─────────────────────────────────────────────────────────────────────
     | DELETE /api/openfda/cache (Admin only — add admin gate as needed)
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Flush the entire OpenFDA cache namespace.
     * Add admin middleware to this route before production use.
     */
    public function flushCache(): JsonResponse
    {
        $this->cache->flush();

        return $this->success(null, 'OpenFDA cache flushed successfully.');
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Response Helpers — consistent JSON envelope
     |──────────────────────────────────────────────────────────────────── */

    private function success(mixed $data, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    private function serviceError(RuntimeException $e): JsonResponse
    {
        $code = $e->getCode();

        // Preserve meaningful HTTP status codes from the client
        $httpStatus = in_array($code, [400, 401, 403, 404, 422, 429, 500, 503], true)
            ? $code
            : 502; // Bad Gateway for unexpected OpenFDA errors

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'data'    => null,
        ], $httpStatus);
    }
}
