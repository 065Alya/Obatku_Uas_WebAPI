<?php

namespace App\Services\Push\Notifications;

use App\Models\ExpiryNotificationLog;
use App\Models\Medicine;
use App\Notifications\NotificationPayloadBuilder;
use App\Services\Push\PushNotificationService;
use Illuminate\Support\Facades\Log;

/**
 * EcomedExpiryNotificationService
 *
 * Dispatches push notifications for medicines nearing or past their
 * expiry date. Complements (and hooks into) the existing EcoMedService
 * expiry logic without duplicating it.
 *
 * Thresholds: H-90, H-30, H-7, expired (0)
 * Dedup: ExpiryNotificationLog (already used by EcoMedService)
 */
final class EcomedExpiryNotificationService
{
    /** Expiry thresholds in days — mirrors EcoMedService::THRESHOLDS */
    private const THRESHOLDS = [90, 30, 7];

    public function __construct(
        private readonly PushNotificationService $push,
    ) {}

    /**
     * Process expiry push notifications for ALL users.
     * Called by the scheduler — wraps EcoMedService notification logic
     * and converts in-app alerts to push notifications as well.
     *
     * @param  bool $dryRun
     * @return int  Notifications dispatched
     */
    public function dispatchAllExpiryAlerts(bool $dryRun = false): int
    {
        $medicines = Medicine::with('owner')
            ->where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(90))
            ->get();

        $dispatched = 0;

        foreach ($medicines as $medicine) {
            $userId = $this->resolveUserId($medicine);
            if (!$userId) continue;

            foreach (self::THRESHOLDS as $days) {
                if ($this->shouldPush($medicine, $userId, $days)) {
                    if (!$dryRun) {
                        $this->dispatchExpiryPush($userId, $medicine, $days);
                    }
                    $dispatched++;
                }
            }

            // Expired medicines
            if ($medicine->isExpired()) {
                if (!ExpiryNotificationLog::alreadySent($userId, $medicine->id, 0)) {
                    if (!$dryRun) {
                        $this->dispatchExpiredPush($userId, $medicine);
                    }
                    $dispatched++;
                }
            }
        }

        return $dispatched;
    }

    /**
     * Dispatch expiry push for a single user (on-demand / testing).
     */
    public function dispatchForUser(int $userId, bool $dryRun = false): int
    {
        $familyMemberIds = \App\Models\FamilyMember::whereHas(
            'family', fn($q) => $q->where('user_id', $userId)
        )->pluck('id');

        $medicines = Medicine::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(90))
            ->where(fn($q) => $q
                ->where(fn($sq) => $sq->where('owner_type', \App\Models\User::class)->where('owner_id', $userId))
                ->orWhere(fn($sq) => $sq->where('owner_type', \App\Models\FamilyMember::class)->whereIn('owner_id', $familyMemberIds))
            )
            ->get();

        $dispatched = 0;

        foreach ($medicines as $medicine) {
            foreach (self::THRESHOLDS as $days) {
                if ($this->shouldPush($medicine, $userId, $days)) {
                    if (!$dryRun) $this->dispatchExpiryPush($userId, $medicine, $days);
                    $dispatched++;
                }
            }

            if ($medicine->isExpired() && !ExpiryNotificationLog::alreadySent($userId, $medicine->id, 0)) {
                if (!$dryRun) $this->dispatchExpiredPush($userId, $medicine);
                $dispatched++;
            }
        }

        return $dispatched;
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Private Helpers
     |──────────────────────────────────────────────────────────────────── */

    private function shouldPush(Medicine $medicine, int $userId, int $days): bool
    {
        if ($medicine->isExpired()) return false;
        if (!$medicine->isExpiringSoon($days)) return false;
        return !ExpiryNotificationLog::alreadySent($userId, $medicine->id, $days);
    }

    private function dispatchExpiryPush(int $userId, Medicine $medicine, int $days): void
    {
        $daysLeft   = (int) now()->diffInDays($medicine->expiry_date);
        $expiryDate = $medicine->expiry_date->translatedFormat('d M Y');

        $payload = NotificationPayloadBuilder::ecomedExpiry(
            medicineName: $medicine->medicine_name,
            expiryDate:   $expiryDate,
            daysLeft:     $daysLeft,
            medicineId:   $medicine->id,
        );

        $this->push->queue($userId, $payload, 'ecomed_expiry');

        Log::info('[EcomedExpiry] Push queued', [
            'user_id'  => $userId,
            'medicine' => $medicine->medicine_name,
            'days'     => $days,
        ]);
    }

    private function dispatchExpiredPush(int $userId, Medicine $medicine): void
    {
        $payload = NotificationPayloadBuilder::ecomedExpiry(
            medicineName: $medicine->medicine_name,
            expiryDate:   $medicine->expiry_date->translatedFormat('d M Y'),
            daysLeft:     0,
            medicineId:   $medicine->id,
        );

        $this->push->queue($userId, $payload, 'ecomed_expiry');

        Log::info('[EcomedExpiry] Expired push queued', [
            'user_id'  => $userId,
            'medicine' => $medicine->medicine_name,
        ]);
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
}
