<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\OpenFda\DrugInteractionService;
use App\Services\OpenFda\DrugSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * V1 OpenFDA Proxy Controller
 *
 * Proxies requests through the service layer — the OpenFDA API key
 * never leaves the server.
 */
class OpenFdaController extends ApiController
{
    public function __construct(
        private readonly DrugSearchService      $searchService,
        private readonly DrugInteractionService $interactionService,
    ) {}

    /**
     * GET /api/v1/openfda/search
     *
     * Query params:
     *   - q       (required) Search term
     *   - limit   (optional) 1–50, default 10
     *   - type    (optional) any|brand|generic
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q'     => 'required|string|min:2|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
            'type'  => 'nullable|in:any,brand,generic',
        ]);

        try {
            $term  = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $type  = $validated['type'] ?? 'any';

            $result = match ($type) {
                'brand'   => $this->searchService->searchByBrand($term, $limit),
                'generic' => $this->searchService->searchByGenericName($term, $limit),
                default   => $this->searchService->search($term, $limit),
            };

            return $this->successResponse($result, 'Pencarian obat berhasil.');

        } catch (RuntimeException $e) {
            return $this->openFdaError($e);
        }
    }

    /**
     * POST /api/v1/openfda/interactions
     *
     * Body:
     *   - drugs[] (required) 2–5 drug names
     */
    public function checkInteractions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'drugs'   => 'required|array|min:2|max:5',
            'drugs.*' => 'required|string|max:100',
        ]);

        try {
            $result = $this->interactionService->checkInteractions($validated['drugs']);

            return $this->successResponse($result, 'Pengecekan interaksi obat selesai.');

        } catch (RuntimeException $e) {
            return $this->openFdaError($e);
        }
    }

    /* ── Private ── */

    private function openFdaError(RuntimeException $e): JsonResponse
    {
        $code = $e->getCode();
        $httpStatus = in_array($code, [400, 401, 403, 404, 422, 429, 500, 503], true) ? $code : 502;

        return $this->errorResponse($e->getMessage(), $httpStatus);
    }
}
