<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\WasteReport;
use App\Services\EcoMedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EcoMedApiController extends Controller
{
    public function __construct(
        protected EcoMedService $ecoMedService,
    ) {}

    /**
     * GET /api/ecomed/stats
     * Returns dashboard stats for the authenticated user.
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->ecoMedService->getDashboardStats($request->user()->id);

        // Strip the Eloquent collections from the JSON response (use counts only)
        return response()->json([
            'expiring_90d'   => $stats['expiring_90d'],
            'expiring_30d'   => $stats['expiring_30d'],
            'expiring_7d'    => $stats['expiring_7d'],
            'expired'        => $stats['expired'],
            'waste_total'    => $stats['waste_total'],
            'waste_verified' => $stats['waste_verified'],
            'waste_quantity' => $stats['waste_quantity'],
        ]);
    }

    /**
     * GET /api/ecomed/expiring
     * Returns paginated list of medicines expiring within ?days= (default 30).
     */
    public function expiring(Request $request): JsonResponse
    {
        $request->validate(['days' => 'nullable|integer|min:1|max:365']);

        $days   = (int) $request->query('days', 30);
        $userId = $request->user()->id;

        $medicines = \App\Repositories\MedicineRepository::class; // just for reference
        $list = \App\Models\Medicine::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where(function ($q) use ($userId) {
                $fmIds = \App\Models\FamilyMember::whereHas('family', fn($fq) => $fq->where('user_id', $userId))->pluck('id');
                $q->where(fn($sq) => $sq->where('owner_type', \App\Models\User::class)->where('owner_id', $userId))
                  ->orWhere(fn($sq) => $sq->where('owner_type', \App\Models\FamilyMember::class)->whereIn('owner_id', $fmIds));
            })
            ->with('category')
            ->orderBy('expiry_date')
            ->paginate(15);

        return response()->json($list);
    }

    /**
     * GET /api/ecomed/expired
     * Returns all expired medicines for the authenticated user.
     */
    public function expired(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $list = Medicine::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->where(function ($q) use ($userId) {
                $fmIds = \App\Models\FamilyMember::whereHas('family', fn($fq) => $fq->where('user_id', $userId))->pluck('id');
                $q->where(fn($sq) => $sq->where('owner_type', \App\Models\User::class)->where('owner_id', $userId))
                  ->orWhere(fn($sq) => $sq->where('owner_type', \App\Models\FamilyMember::class)->whereIn('owner_id', $fmIds));
            })
            ->with('category')
            ->orderBy('expiry_date')
            ->get();

        return response()->json(['data' => $list]);
    }

    /**
     * GET /api/ecomed/disposal-guides
     * Returns all active disposal guides.
     */
    public function disposalGuides(Request $request): JsonResponse
    {
        $guides = $this->ecoMedService->getAllGuides();
        return response()->json(['data' => $guides]);
    }

    /**
     * GET /api/ecomed/disposal-guides/{form}
     * Returns disposal guide for a specific medicine form.
     */
    public function disposalGuideByForm(Request $request, string $form): JsonResponse
    {
        $guide = $this->ecoMedService->getGuideForForm($form);

        if (!$guide) {
            return response()->json(['message' => 'Panduan pembuangan tidak ditemukan untuk bentuk obat ini.'], 404);
        }

        return response()->json(['data' => $guide]);
    }

    /**
     * GET /api/ecomed/waste-reports
     * Returns paginated waste reports for the authenticated user.
     */
    public function wasteReports(Request $request): JsonResponse
    {
        $reports = $this->ecoMedService->getUserWasteReports($request->user()->id);
        return response()->json($reports);
    }

    /**
     * POST /api/ecomed/waste-reports
     * Create a new waste report.
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

        return response()->json([
            'message' => 'Laporan pembuangan berhasil dicatat.',
            'data'    => $report,
        ], 201);
    }

    /**
     * POST /api/ecomed/check-expiry
     * Manually trigger expiry notification check for the authenticated user.
     */
    public function checkExpiry(Request $request): JsonResponse
    {
        $dispatched = $this->ecoMedService->processUserExpiryNotifications($request->user()->id);

        return response()->json([
            'dispatched' => $dispatched,
            'message'    => $dispatched > 0
                ? "{$dispatched} notifikasi kedaluwarsa baru dikirim."
                : 'Tidak ada notifikasi kedaluwarsa baru.',
        ]);
    }

    /**
     * GET /api/ecomed/notification-history
     * Returns expiry notification log history for the user.
     */
    public function notificationHistory(Request $request): JsonResponse
    {
        $history = $this->ecoMedService->getNotificationHistory($request->user()->id);
        return response()->json($history);
    }
}
