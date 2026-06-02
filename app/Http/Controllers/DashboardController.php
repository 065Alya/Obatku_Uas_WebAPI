<?php

namespace App\Http\Controllers;

use App\Services\MedicineService;
use App\Services\ScheduleService;
use App\Services\FamilyService;
use App\Services\EcoMedService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected MedicineService $medicineService,
        protected ScheduleService $scheduleService,
        protected FamilyService $familyService,
        protected EcoMedService $ecoMedService,
    ) {}

    /**
     * User dashboard.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $data = [
            'todaySchedules'    => $this->scheduleService->getTodaySchedules($userId),
            'upcomingSchedules' => $this->scheduleService->getUpcomingSchedules($userId),
            'alerts'            => $this->medicineService->getAlertSummary($userId),
            'familyMembers'     => $this->familyService->getActiveFamily($userId),
            'adherenceRate'     => $this->scheduleService->getAdherenceRate($userId),
            'recentActivity'    => ActivityLogService::getRecent($userId, 5),
            'medicineCount'     => $this->medicineService->getUserMedicines($userId, 1)->total(),
        ];

        return view('dashboard.index', $data);
    }

    /**
     * Admin dashboard — includes EcoMed statistics (Phase 3, Feature 9).
     */
    public function adminIndex()
    {
        // ── Core Counts ──────────────────────────────────────────────────────
        $totalUsers     = \App\Models\User::where('role', 'user')->count();
        $totalMedicines = \App\Models\Medicine::count();
        $totalSchedules = \App\Models\MedicineSchedule::where('is_active', true)->count();
        $totalArticles  = \App\Models\HealthArticle::count();

        // ── EcoMed System Stats (Feature 9) ───────────────────────────────────
        $ecoStats = $this->ecoMedService->getSystemEcoStats();

        // Fallback if method not yet available
        if (!$ecoStats) {
            $wReports  = \App\Models\WasteReport::count();
            $totalQty  = \App\Models\WasteReport::sum('quantity');
            $verified  = \App\Models\WasteReport::where('status', 'verified')->count();
            $ecoStats  = [
                'total_waste_reports'      => $wReports,
                'total_quantity_disposed'  => round((float) $totalQty, 2),
                'verified_reports'         => $verified,
                'waste_prevented_estimate' => round((float) $totalQty * 0.85, 2), // ~85% proper disposal rate
                'waste_rate_pct'           => $wReports > 0 ? round(($verified / $wReports) * 100, 1) : 0,
                'expired_medicines'        => \App\Models\Medicine::where('expiry_date', '<', now())->count(),
                'expiring_soon'            => \App\Models\Medicine::whereBetween('expiry_date', [now(), now()->addDays(30)])->count(),
                'disposal_methods'         => \App\Models\WasteReport::selectRaw('disposal_method, count(*) as total')
                    ->groupBy('disposal_method')
                    ->orderByDesc('total')
                    ->pluck('total', 'disposal_method')
                    ->toArray(),
                'top_users'                => \App\Models\WasteReport::selectRaw('user_id, count(*) as total')
                    ->with('user:id,name,email')
                    ->groupBy('user_id')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get(),
            ];
        }

        $data = [
            'totalUsers'     => $totalUsers,
            'totalMedicines' => $totalMedicines,
            'totalSchedules' => $totalSchedules,
            'totalArticles'  => $totalArticles,
            'ecoStats'       => $ecoStats,
            'recentUsers'    => \App\Models\User::where('role', 'user')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
            'recentActivity' => \App\Models\ActivityLog::with('user')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
        ];

        return view('admin.dashboard', $data);
    }
}
