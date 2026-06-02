<?php

namespace App\Services\Push\Notifications;

use App\Models\Medicine;
use App\Models\MedicineSchedule;
use App\Notifications\NotificationPayloadBuilder;
use App\Services\Push\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * MedicineReminderNotificationService
 *
 * Scans due medicine schedules and dispatches push reminders.
 * Replaces the raw logic previously embedded in SendMedicineReminders command.
 * The Artisan command delegates to this service for testability.
 */
final class MedicineReminderNotificationService
{
    public function __construct(
        private readonly PushNotificationService $push,
    ) {}

    /**
     * Dispatch reminders for all schedules due within the given window.
     *
     * @param  int      $windowMinutes  Look-ahead window in minutes
     * @param  int|null $userId         Limit to one user (null = all)
     * @param  bool     $dryRun         Preview without dispatching
     * @return int  Number of notifications dispatched
     */
    public function dispatchDueReminders(int $windowMinutes = 15, ?int $userId = null, bool $dryRun = false): int
    {
        $now         = Carbon::now('Asia/Jakarta');
        $windowStart = $now->format('H:i:s');
        $windowEnd   = $now->copy()->addMinutes($windowMinutes)->format('H:i:s');

        $query = MedicineSchedule::with(['user.pushSubscriptions', 'medicine'])
            ->where('is_active', true)
            ->where('start_date', '<=', $now->toDateString())
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $now->toDateString()))
            ->whereHas('user.pushSubscriptions', fn($q) => $q->where('is_active', true))
            ->whereRaw('TIME(schedule_time) BETWEEN ? AND ?', [$windowStart, $windowEnd]);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $schedules  = $query->get();
        $dispatched = 0;

        foreach ($schedules as $schedule) {
            $user     = $schedule->user;
            $medicine = $schedule->medicine;

            if (!$user || !$medicine) continue;

            // Dedup: prevent re-dispatch within same 15-minute window
            $cacheKey = "push_reminder_{$schedule->id}_{$now->format('Y-m-d_Hi')}";
            if (Cache::has($cacheKey)) continue;

            $payload = NotificationPayloadBuilder::medicineReminder(
                medicineName: $medicine->medicine_name,
                dosageLabel:  (string) ($schedule->dosage_amount ?? ''),
                scheduleId:   $schedule->id,
                medicineId:   $medicine->id,
            );

            if ($dryRun) {
                Log::info('[MedicineReminder][DRY RUN] Would send', [
                    'user_id'     => $user->id,
                    'medicine'    => $medicine->medicine_name,
                    'schedule_id' => $schedule->id,
                ]);
                $dispatched++;
                continue;
            }

            $this->push->queue($user->id, $payload, 'medicine_reminder');
            Cache::put($cacheKey, true, now()->addMinutes(12));
            $dispatched++;

            Log::info('[MedicineReminder] Queued', [
                'user_id'     => $user->id,
                'medicine'    => $medicine->medicine_name,
                'schedule_id' => $schedule->id,
            ]);
        }

        return $dispatched;
    }
}
