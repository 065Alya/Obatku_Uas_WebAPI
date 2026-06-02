<?php

namespace App\Services\Push\Notifications;

use App\Models\Medicine;
use App\Models\User;
use App\Notifications\NotificationPayloadBuilder;
use App\Services\Push\PushNotificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * StockAlertNotificationService
 *
 * Scans all active medicines for low/zero stock and dispatches
 * push alerts to their owners. Rate-limited to once per N days
 * per medicine to avoid spamming the user.
 */
final class StockAlertNotificationService
{
    /** Cache TTL key for "already alerted today" dedup. */
    private int $resendAfterDays;

    public function __construct(
        private readonly PushNotificationService $push,
    ) {
        $this->resendAfterDays = (int) config('notifications.stock.resend_after_days', 3);
    }

    /**
     * Check all medicines for low stock and dispatch push alerts.
     *
     * @param  int|null $userId  Limit to one user (null = all users)
     * @param  bool     $dryRun
     * @return int  Number of alerts dispatched
     */
    public function dispatchLowStockAlerts(?int $userId = null, bool $dryRun = false): int
    {
        $query = Medicine::with('owner')
            ->where('is_active', true)
            ->whereRaw('stock <= stock_alert');  // stock at or below threshold

        if ($userId !== null) {
            $query->where(fn($q) => $q
                ->where(fn($sq) => $sq->where('owner_type', \App\Models\User::class)->where('owner_id', $userId))
                ->orWhere(fn($sq) => $sq->where('owner_type', \App\Models\FamilyMember::class)
                    ->whereIn('owner_id', \App\Models\FamilyMember::whereHas(
                        'family', fn($fq) => $fq->where('user_id', $userId)
                    )->pluck('id')))
            );
        }

        $medicines  = $query->get();
        $dispatched = 0;

        foreach ($medicines as $medicine) {
            $ownerId = $this->resolveUserId($medicine);
            if (!$ownerId) continue;

            // Dedup: don't re-alert for same medicine within resend window
            $cacheKey = "push_stock_alert_{$medicine->id}";
            if (Cache::has($cacheKey)) continue;

            $payload = NotificationPayloadBuilder::stockAlert(
                medicineName:  $medicine->medicine_name,
                currentStock:  (int) $medicine->stock,
                threshold:     (int) $medicine->stock_alert,
                medicineId:    $medicine->id,
            );

            if ($dryRun) {
                Log::info('[StockAlert][DRY RUN] Would send', [
                    'user_id'    => $ownerId,
                    'medicine'   => $medicine->medicine_name,
                    'stock'      => $medicine->stock,
                    'threshold'  => $medicine->stock_alert,
                ]);
                $dispatched++;
                continue;
            }

            $this->push->queue($ownerId, $payload, 'stock_alert');
            Cache::put($cacheKey, true, now()->addDays($this->resendAfterDays));
            $dispatched++;

            Log::info('[StockAlert] Queued', [
                'user_id'   => $ownerId,
                'medicine'  => $medicine->medicine_name,
                'stock'     => $medicine->stock,
            ]);
        }

        return $dispatched;
    }

    /**
     * Immediately dispatch a stock alert for a single medicine.
     * Called synchronously when a medicine's stock is updated via UI.
     */
    public function alertForMedicine(Medicine $medicine): void
    {
        if (!$medicine->isLowStock()) return;

        $ownerId = $this->resolveUserId($medicine);
        if (!$ownerId) return;

        $payload = NotificationPayloadBuilder::stockAlert(
            medicineName:  $medicine->medicine_name,
            currentStock:  (int) $medicine->stock,
            threshold:     (int) $medicine->stock_alert,
            medicineId:    $medicine->id,
        );

        $this->push->queue($ownerId, $payload, 'stock_alert');
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
