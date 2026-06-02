<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\ExpiryNotificationLog;
use App\Models\Medicine;
use App\Models\WasteReport;
use App\Repositories\Contracts\EcoMedRepositoryInterface;
use App\Repositories\MedicineRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as BaseCollection;

class EcoMedService
{
    /** Expiry thresholds in days — order matters (largest first for queries). */
    public const THRESHOLDS = [90, 30, 7];

    public function __construct(
        protected MedicineRepository    $medicineRepo,
        protected EcoMedRepositoryInterface $ecoMedRepo,
    ) {}

    /* ─────────────────────────────────────────────────────────────────────
     | EXPIRY MANAGEMENT
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Full expiry dashboard stats: categorised by H-90 / H-30 / H-7 / expired.
     */
    public function getDashboardStats(int $userId): array
    {
        $expiring90 = $this->medicineRepo->getExpiringSoon($userId, 90);
        $expiring30 = $this->medicineRepo->getExpiringSoon($userId, 30);
        $expiring7  = $this->medicineRepo->getExpiringSoon($userId, 7);
        $expired    = $this->medicineRepo->getExpired($userId);

        $wasteStats = $this->ecoMedRepo->getWasteStatsByUser($userId);

        return [
            // Counts
            'expiring_90d'     => $expiring90->count(),
            'expiring_30d'     => $expiring30->count(),
            'expiring_7d'      => $expiring7->count(),
            'expired'          => $expired->count(),

            // Full collections
            'expiring_90_list' => $expiring90,
            'expiring_30_list' => $expiring30,
            'expiring_7_list'  => $expiring7,
            'expired_list'     => $expired,

            // Waste tracking
            'waste_total'      => $wasteStats['total'],
            'waste_verified'   => $wasteStats['verified'],
            'waste_pending'    => $wasteStats['pending'],
            'waste_quantity'   => $wasteStats['total_quantity'],
        ];
    }

    /**
     * Get medicines grouped by expiry urgency band.
     * Returns ['urgent'=>[], 'warning'=>[], 'notice'=>[], 'expired'=>[]]
     */
    public function getExpiryCategorised(int $userId): array
    {
        $expiring90 = $this->medicineRepo->getExpiringSoon($userId, 90);
        $expired    = $this->medicineRepo->getExpired($userId);

        return [
            'expired' => $expired,
            'urgent'  => $expiring90->filter(fn($m) => $m->expiry_date->diffInDays(now()) <= 7),
            'warning' => $expiring90->filter(fn($m) => $m->expiry_date->diffInDays(now()) > 7 && $m->expiry_date->diffInDays(now()) <= 30),
            'notice'  => $expiring90->filter(fn($m) => $m->expiry_date->diffInDays(now()) > 30),
        ];
    }

    /* ─────────────────────────────────────────────────────────────────────
     | EXPIRY NOTIFICATION ENGINE
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Process expiry alerts for ALL users — called by the scheduled command.
     * Returns count of notifications dispatched.
     */
    public function processAllExpiryNotifications(): int
    {
        $dispatched = 0;

        // Get all medicines expiring within 90 days or already expired,
        // grouped by owner (user_id resolved via medicine ownership)
        $medicines = Medicine::with('owner')
            ->where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(90))
            ->get();

        foreach ($medicines as $medicine) {
            $userId = $this->resolveUserId($medicine);
            if (!$userId) continue;

            foreach (self::THRESHOLDS as $days) {
                if ($this->shouldNotify($medicine, $userId, $days)) {
                    $this->dispatchExpiryAlert($userId, $medicine, $days);
                    $dispatched++;
                }
            }

            // Handle already-expired
            if ($medicine->isExpired()) {
                if (!ExpiryNotificationLog::alreadySent($userId, $medicine->id, 0)) {
                    $this->dispatchExpiredAlert($userId, $medicine);
                    $dispatched++;
                }
            }
        }

        return $dispatched;
    }

    /**
     * Process expiry alerts for a single user — callable on-demand.
     */
    public function processUserExpiryNotifications(int $userId): int
    {
        $dispatched = 0;

        $medicines = Medicine::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(90))
            ->where(function ($q) use ($userId) {
                $familyMemberIds = \App\Models\FamilyMember::whereHas('family', fn($fq) => $fq->where('user_id', $userId))
                    ->pluck('id');
                $q->where(fn($sq) => $sq->where('owner_type', \App\Models\User::class)->where('owner_id', $userId))
                  ->orWhere(fn($sq) => $sq->where('owner_type', \App\Models\FamilyMember::class)->whereIn('owner_id', $familyMemberIds));
            })
            ->get();

        foreach ($medicines as $medicine) {
            foreach (self::THRESHOLDS as $days) {
                if ($this->shouldNotify($medicine, $userId, $days)) {
                    $this->dispatchExpiryAlert($userId, $medicine, $days);
                    $dispatched++;
                }
            }
            if ($medicine->isExpired() && !ExpiryNotificationLog::alreadySent($userId, $medicine->id, 0)) {
                $this->dispatchExpiredAlert($userId, $medicine);
                $dispatched++;
            }
        }

        return $dispatched;
    }

    /* ─────────────────────────────────────────────────────────────────────
     | DISPOSAL GUIDE
     |──────────────────────────────────────────────────────────────────── */

    public function getAllGuides(): Collection
    {
        return $this->ecoMedRepo->getAllActiveGuides();
    }

    public function getGuideForForm(string $form): ?\App\Models\DisposalGuide
    {
        return $this->ecoMedRepo->getGuideByForm($form);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | WASTE REPORTS
     |──────────────────────────────────────────────────────────────────── */

    public function getUserWasteReports(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->ecoMedRepo->getUserWasteReports($userId, $perPage);
    }

    public function createWasteReport(int $userId, array $data): WasteReport
    {
        return $this->ecoMedRepo->createWasteReport($userId, $data);
    }

    public function getAllWasteReports(int $perPage = 15): LengthAwarePaginator
    {
        return $this->ecoMedRepo->getAllWasteReports($perPage);
    }

    public function updateReportStatus(int $reportId, string $status): WasteReport
    {
        return $this->ecoMedRepo->updateReportStatus($reportId, $status);
    }

    public function getWasteStatsByUser(int $userId): array
    {
        return $this->ecoMedRepo->getWasteStatsByUser($userId);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | NOTIFICATION HISTORY
     |──────────────────────────────────────────────────────────────────── */

    public function getNotificationHistory(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->ecoMedRepo->getExpiryNotificationHistory($userId, $perPage);
    }

    public function getNotificationStats(int $userId): array
    {
        return $this->ecoMedRepo->getNotificationStats($userId);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | SYSTEM STATS (Admin)
     |──────────────────────────────────────────────────────────────────── */

    public function getSystemEcoStats(): array
    {
        $reports  = WasteReport::where('status', 'verified');
        $quantity = (clone $reports)->sum('quantity');
        $methods  = (clone $reports)
            ->selectRaw('disposal_method, count(*) as total')
            ->groupBy('disposal_method')
            ->pluck('total', 'disposal_method');

        return [
            'total_reports'   => WasteReport::count(),
            'verified_reports'=> (clone $reports)->count(),
            'total_quantity'  => (float) $quantity,
            'by_method'       => $methods,
            'users_reporting' => WasteReport::distinct('user_id')->count('user_id'),
            'notifications_sent' => ExpiryNotificationLog::count(),
        ];
    }

    /* ─────────────────────────────────────────────────────────────────────
     | PRIVATE HELPERS
     |──────────────────────────────────────────────────────────────────── */

    private function shouldNotify(Medicine $medicine, int $userId, int $days): bool
    {
        if ($medicine->isExpired()) return false;
        if (!$medicine->isExpiringSoon($days)) return false;
        return !ExpiryNotificationLog::alreadySent($userId, $medicine->id, $days);
    }

    private function resolveUserId(Medicine $medicine): ?int
    {
        if ($medicine->owner_type === \App\Models\User::class) {
            return $medicine->owner_id;
        }
        if ($medicine->owner_type === \App\Models\FamilyMember::class) {
            return $medicine->owner?->family?->user_id;
        }
        return null;
    }

    private function dispatchExpiryAlert(int $userId, Medicine $medicine, int $days): void
    {
        $daysLeft = now()->diffInDays($medicine->expiry_date);
        $label    = "H-{$days}";

        $severity = match (true) {
            $days <= 7  => Alert::SEVERITY_DANGER,
            $days <= 30 => Alert::SEVERITY_WARNING,
            default     => Alert::SEVERITY_INFO,
        };

        // Create in-app alert
        Alert::create([
            'user_id'        => $userId,
            'type'           => Alert::TYPE_REMINDER,
            'severity'       => $severity,
            'message'        => "⏰ {$label} | Obat \"{$medicine->medicine_name}\" akan kedaluwarsa pada "
                               . $medicine->expiry_date->translatedFormat('d F Y')
                               . " ({$daysLeft} hari lagi). Segera periksa dan rencanakan pembuangan yang aman.",
            'is_read'        => false,
            'alertable_type' => Medicine::class,
            'alertable_id'   => $medicine->id,
        ]);

        // Log the notification to prevent duplication
        ExpiryNotificationLog::record(
            userId:          $userId,
            medicineId:      $medicine->id,
            channel:         'database',
            expiryDate:      $medicine->expiry_date->toDateString(),
            daysThreshold:   $days,
            resendAfterDays: $this->resendAfterDays($days),
        );
    }

    private function dispatchExpiredAlert(int $userId, Medicine $medicine): void
    {
        Alert::create([
            'user_id'        => $userId,
            'type'           => Alert::TYPE_REMINDER,
            'severity'       => Alert::SEVERITY_DANGER,
            'message'        => "🚨 KEDALUWARSA | Obat \"{$medicine->medicine_name}\" sudah melewati tanggal kedaluwarsa "
                               . "({$medicine->expiry_date->translatedFormat('d F Y')}). "
                               . "Jangan dikonsumsi — gunakan panduan pembuangan EcoMed untuk membuang dengan aman.",
            'is_read'        => false,
            'alertable_type' => Medicine::class,
            'alertable_id'   => $medicine->id,
        ]);

        ExpiryNotificationLog::record(
            userId:          $userId,
            medicineId:      $medicine->id,
            channel:         'database',
            expiryDate:      $medicine->expiry_date->toDateString(),
            daysThreshold:   0,
            resendAfterDays: 30, // re-notify about expired every 30 days
        );
    }

    private function resendAfterDays(int $threshold): int
    {
        return match ($threshold) {
            7  => 3,
            30 => 7,
            90 => 14,
            default => 7,
        };
    }
}
