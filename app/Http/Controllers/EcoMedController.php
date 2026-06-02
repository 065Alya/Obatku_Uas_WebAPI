<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\WasteReport;
use App\Services\ActivityLogService;
use App\Services\EcoMedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EcoMedController extends Controller
{
    public function __construct(
        protected EcoMedService $ecoMedService,
    ) {}

    /* ─── Dashboard ─── */

    public function index(Request $request): View
    {
        $userId    = $request->user()->id;
        $stats     = $this->ecoMedService->getDashboardStats($userId);
        $guides    = $this->ecoMedService->getAllGuides();
        $categorised = $this->ecoMedService->getExpiryCategorised($userId);

        return view('ecomed.index', compact('stats', 'guides', 'categorised'));
    }

    /* ─── Expiry Alerts ─── */

    public function expiryAlerts(Request $request): View
    {
        $userId      = $request->user()->id;
        $stats       = $this->ecoMedService->getDashboardStats($userId);
        $categorised = $this->ecoMedService->getExpiryCategorised($userId);
        $notifStats  = $this->ecoMedService->getNotificationStats($userId);

        return view('ecomed.expiry-alerts', compact('stats', 'categorised', 'notifStats'));
    }

    /* ─── Disposal Guide ─── */

    public function disposalGuide(Request $request): View
    {
        $guides = $this->ecoMedService->getAllGuides();
        $form   = $request->query('form');
        $guide  = $form ? $this->ecoMedService->getGuideForForm($form) : null;

        return view('ecomed.disposal-guide', compact('guides', 'guide', 'form'));
    }

    /* ─── Waste Reports ─── */

    public function wasteReports(Request $request): View
    {
        $userId    = $request->user()->id;
        $reports   = $this->ecoMedService->getUserWasteReports($userId);
        $wasteStats = $this->ecoMedService->getWasteStatsByUser($userId);

        $medicines = Medicine::where('owner_type', \App\Models\User::class)
            ->where('owner_id', $userId)
            ->orderBy('medicine_name')
            ->get();

        return view('ecomed.waste-reports', compact('reports', 'medicines', 'wasteStats'));
    }

    public function storeWasteReport(Request $request): RedirectResponse
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

        $this->ecoMedService->createWasteReport($request->user()->id, $validated);

        ActivityLogService::log(
            'create',
            "Melaporkan pembuangan obat: {$validated['medicine_name']} ({$validated['quantity']} {$validated['unit']}).",
            'WasteReport'
        );

        return redirect()->route('ecomed.waste-reports')
            ->with('success', '🌿 Laporan pembuangan obat berhasil dicatat. Terima kasih telah menjaga lingkungan!');
    }

    /* ─── Notification History ─── */

    public function notificationHistory(Request $request): View
    {
        $userId  = $request->user()->id;
        $history = $this->ecoMedService->getNotificationHistory($userId);
        $stats   = $this->ecoMedService->getNotificationStats($userId);

        return view('ecomed.notification-history', compact('history', 'stats'));
    }

    /* ─── AJAX: trigger manual notification check ─── */

    public function checkExpiry(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $dispatched = $this->ecoMedService->processUserExpiryNotifications($userId);

        return response()->json([
            'success'    => true,
            'dispatched' => $dispatched,
            'message'    => $dispatched > 0
                ? "✅ {$dispatched} notifikasi kedaluwarsa baru dikirim."
                : '✅ Tidak ada notifikasi kedaluwarsa baru saat ini.',
        ]);
    }

    /* ─── CSV Export ─── */

    /**
     * GET /ecomed/report/export
     * Export user's waste reports as a native CSV download — no package needed.
     */
    public function exportCsv(Request $request)
    {
        $userId  = $request->user()->id;
        $reports = $this->ecoMedService->getUserWasteReports($userId, 9999);
        $stats   = $this->ecoMedService->getWasteStatsByUser($userId);

        $filename = 'obatku-ecomed-laporan-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache',
            'Pragma'              => 'no-cache',
        ];

        $callback = function () use ($reports, $stats) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fputs($output, "\xEF\xBB\xBF");

            // Summary header
            fputcsv($output, ['Ringkasan EcoMed ObatKu']);
            fputcsv($output, ['Diekspor pada', now()->translatedFormat('d F Y, H:i')]);
            fputcsv($output, ['Total Laporan',  $stats['total']]);
            fputcsv($output, ['Terverifikasi',  $stats['verified']]);
            fputcsv($output, ['Total Kuantitas', $stats['total_quantity']]);
            fputcsv($output, []);

            // Column headers
            fputcsv($output, [
                'No', 'Nama Obat', 'Bentuk Sediaan', 'Kuantitas', 'Satuan',
                'Metode Pembuangan', 'Tanggal Pembuangan', 'Status', 'Catatan', 'Dibuat Pada',
            ]);

            foreach ($reports->items() as $i => $r) {
                fputcsv($output, [
                    $i + 1,
                    $r->medicine_name,
                    $r->medicine_form,
                    $r->quantity,
                    $r->unit,
                    ucwords(str_replace('_', ' ', $r->disposal_method)),
                    $r->disposed_at ? $r->disposed_at->format('d/m/Y') : '—',
                    ucfirst($r->status ?? 'pending'),
                    $r->notes ?? '',
                    $r->created_at ? $r->created_at->format('d/m/Y H:i') : '—',
                ]);
            }

            fclose($output);
        };

        ActivityLogService::log('export', 'Mengekspor laporan EcoMed (CSV).', 'WasteReport');

        return response()->stream($callback, 200, $headers);
    }

    /**
     * GET /admin/ecomed/export-csv
     * Admin-only: export ALL waste reports across all users.
     */
    public function adminExportCsv(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $reports  = $this->ecoMedService->getAllWasteReports(9999);
        $filename = 'obatku-admin-ecomed-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache',
        ];

        $callback = function () use ($reports) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");

            fputcsv($output, ['Laporan EcoMed — Admin Export', now()->translatedFormat('d F Y')]);
            fputcsv($output, []);
            fputcsv($output, [
                'No', 'User ID', 'Nama Pengguna', 'Email',
                'Nama Obat', 'Bentuk', 'Kuantitas', 'Satuan',
                'Metode Pembuangan', 'Tanggal Pembuangan', 'Status', 'Catatan',
            ]);

            foreach ($reports->items() as $i => $r) {
                fputcsv($output, [
                    $i + 1,
                    $r->user_id,
                    $r->user?->name ?? '—',
                    $r->user?->email ?? '—',
                    $r->medicine_name,
                    $r->medicine_form,
                    $r->quantity,
                    $r->unit,
                    ucwords(str_replace('_', ' ', $r->disposal_method)),
                    $r->disposed_at ? $r->disposed_at->format('d/m/Y') : '—',
                    ucfirst($r->status ?? 'pending'),
                    $r->notes ?? '',
                ]);
            }

            fclose($output);
        };

        ActivityLogService::log('export', 'Admin mengekspor semua laporan EcoMed (CSV).', 'WasteReport');

        return response()->stream($callback, 200, $headers);
    }
}
