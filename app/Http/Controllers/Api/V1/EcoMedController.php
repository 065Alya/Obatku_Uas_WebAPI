<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\EcoMedService;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EcoMedController extends ApiController
{
    public function __construct(protected EcoMedService $ecoMedService) {}

    /**
     * GET /api/v1/ecomed/stats
     * Dashboard statistics: expired count, expiring soon counts, waste totals.
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->ecoMedService->getDashboardStats($request->user()->id);

        return $this->successResponse($stats);
    }

    /**
     * GET /api/v1/ecomed/expiring
     * Medicines expiring soon, categorised by urgency.
     *
     * Query: ?days=30  (default 90 to return all 3 tiers)
     */
    public function expiring(Request $request): JsonResponse
    {
        $categorised = $this->ecoMedService->getExpiryCategorised($request->user()->id);

        return $this->successResponse($categorised);
    }

    /**
     * GET /api/v1/ecomed/disposal-guide/{type}
     * Return the disposal guide for a specific medicine form (tablet, syrup, etc.)
     */
    public function disposalGuide(Request $request, string $type): JsonResponse
    {
        $guide = $this->ecoMedService->getGuideForForm($type);

        if (!$guide) {
            return $this->errorResponse("Panduan pembuangan untuk bentuk '{$type}' tidak ditemukan.", 404);
        }

        return $this->successResponse([
            'id'          => $guide->id,
            'form'        => $guide->medicine_form,
            'title'       => $guide->title,
            'steps'       => $guide->steps,
            'safety_note' => $guide->safety_note ?? null,
            'is_active'   => $guide->is_active,
        ]);
    }

    /**
     * GET /api/v1/ecomed/report
     * Paginated waste reports for the authenticated user.
     * Query params: ?page=1&per_page=20
     */
    public function report(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = min($perPage, 100); // cap at 100

        $reports = $this->ecoMedService->getUserWasteReports($request->user()->id, $perPage);
        $stats   = $this->ecoMedService->getWasteStatsByUser($request->user()->id);

        $items = collect($reports->items())->map(fn($r) => [
            'id'              => $r->id,
            'medicine_name'   => $r->medicine_name,
            'medicine_form'   => $r->medicine_form,
            'quantity'        => $r->quantity,
            'unit'            => $r->unit,
            'disposal_method' => $r->disposal_method,
            'disposed_at'     => $r->disposed_at?->toDateString(),
            'status'          => $r->status,
            'notes'           => $r->notes,
            'created_at'      => $r->created_at?->toIso8601String(),
        ]);

        return $this->successResponse([
            'stats'      => $stats,
            'reports'    => $items,
            'pagination' => [
                'current_page' => $reports->currentPage(),
                'last_page'    => $reports->lastPage(),
                'per_page'     => $reports->perPage(),
                'total'        => $reports->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/ecomed/waste-reports
     * Log a disposal/waste report.
     */
    public function storeWasteReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'medicine_id'     => 'nullable|exists:medicines,id',
            'medicine_name'   => 'required|string|max:255',
            'medicine_form'   => 'required|string|max:100',
            'quantity'        => 'required|numeric|min:0.01',
            'unit'            => 'required|string|max:50',
            'disposal_method' => 'required|in:pharmacy_return,household_trash,collection_point,flush,bury',
            'notes'           => 'nullable|string|max:1000',
            'disposed_at'     => 'required|date|before_or_equal:today',
        ]);

        $report = $this->ecoMedService->createWasteReport($request->user()->id, $validated);

        ActivityLogService::log(
            'create',
            "API: Laporan pembuangan obat: {$validated['medicine_name']} ({$validated['quantity']} {$validated['unit']})",
            'WasteReport',
            $report->id
        );

        return $this->successResponse([
            'id'              => $report->id,
            'medicine_name'   => $report->medicine_name,
            'quantity'        => $report->quantity,
            'unit'            => $report->unit,
            'disposal_method' => $report->disposal_method,
            'disposed_at'     => $report->disposed_at?->toDateString(),
            'created_at'      => $report->created_at?->toISOString(),
        ], 'Laporan pembuangan obat berhasil dicatat.', 201);
    }
}
