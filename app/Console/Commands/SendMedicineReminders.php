<?php

namespace App\Console\Commands;

use App\Jobs\SendPushNotification;
use App\Models\MedicineSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * php artisan pwa:send-medicine-reminders
 *
 * Scans today's active medicine schedules and dispatches push notification
 * jobs for users whose next dose is due within the reminder window.
 *
 * Scheduled via routes/console.php — runs every 15 minutes between 06:00–23:00 WIB.
 *
 * Schema reference (medicine_schedules table):
 *   - schedule_time  TIME         — the time the dose is due (HH:MM:SS)
 *   - dosage_amount  VARCHAR(100) — dose description, e.g. "1 tablet"
 *   - is_active      BOOLEAN
 *   - start_date     DATE
 *   - end_date       DATE nullable
 */
class SendMedicineReminders extends Command
{
    protected $signature = 'pwa:send-medicine-reminders
                                {--dry-run : Preview without dispatching jobs}
                                {--user=   : Only send for a specific user ID}
                                {--window= : Look-ahead window in minutes (default 15)}';

    protected $description = 'Send push notification reminders for upcoming medicine schedules';

    /** Default look-ahead window in minutes. */
    private const DEFAULT_WINDOW = 15;

    public function handle(): int
    {
        $now     = Carbon::now('Asia/Jakarta');
        $dryRun  = $this->option('dry-run');
        $userId  = $this->option('user');
        $window  = (int) ($this->option('window') ?? self::DEFAULT_WINDOW);

        $this->info(sprintf(
            '[%s] Checking medicine reminders (window: +%d min)%s',
            $now->format('Y-m-d H:i'),
            $window,
            $dryRun ? ' [DRY RUN]' : ''
        ));

        // Time window: from now to now + window
        $windowStart = $now->format('H:i:s');
        $windowEnd   = $now->copy()->addMinutes($window)->format('H:i:s');

        // Fetch active schedules in the window that have push-enabled users
        $query = MedicineSchedule::with(['user.pushSubscriptions', 'medicine'])
            ->where('is_active', true)
            ->where('start_date', '<=', $now->toDateString())
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $now->toDateString()))
            ->whereHas('user.pushSubscriptions', fn ($q) => $q->where('is_active', true))
            ->whereRaw('TIME(schedule_time) BETWEEN ? AND ?', [$windowStart, $windowEnd]);

        if ($userId) {
            $query->where('user_id', (int) $userId);
        }

        $schedules = $query->get();

        if ($schedules->isEmpty()) {
            $this->line('No reminders due in this window.');
            return self::SUCCESS;
        }

        $dispatched = 0;

        foreach ($schedules as $schedule) {
            $user     = $schedule->user;
            $medicine = $schedule->medicine;

            if (!$user || !$medicine) {
                continue;
            }

            // Dedup: skip if we already sent this reminder in this cron tick
            $cacheKey = "pwa_reminder_{$schedule->id}_{$now->format('Y-m-d_Hi')}";
            if (Cache::has($cacheKey)) {
                $this->line("  ↷ Skipping #{$schedule->id} (already dispatched this window)");
                continue;
            }

            $dosageLabel = $schedule->dosage_amount
                ? " — {$schedule->dosage_amount}"
                : '';

            $payload = [
                'title' => '⏰ Pengingat Minum Obat',
                'body'  => "Saatnya minum {$medicine->name}{$dosageLabel}",
                'url'   => '/schedules',
                'icon'  => '/icons/icon-192x192.png',
                'badge' => '/icons/icon-72x72.png',
                'tag'   => "medicine-reminder-{$schedule->id}",
                'data'  => [
                    'schedule_id' => $schedule->id,
                    'medicine_id' => $medicine->id,
                    'type'        => 'medicine_reminder',
                ],
                'actions' => [
                    ['action' => 'open',    'title' => 'Buka Jadwal'],
                    ['action' => 'dismiss', 'title' => 'Nanti'],
                ],
                'requireInteraction' => true,
                'vibrate'            => [300, 100, 300, 100, 300],
            ];

            if ($dryRun) {
                $this->line(sprintf(
                    '  [DRY RUN] Would notify user #%d: %s @ %s',
                    $user->id,
                    $medicine->name,
                    $schedule->schedule_time
                ));
                $dispatched++;
                continue;
            }

            SendPushNotification::dispatch($user->id, $payload)
                ->onQueue('notifications');

            // Prevent re-dispatch for the next ~12 minutes
            Cache::put($cacheKey, true, now()->addMinutes(12));

            $dispatched++;

            Log::info('[MedicineReminder] Dispatched push', [
                'user_id'     => $user->id,
                'medicine'    => $medicine->name,
                'schedule_id' => $schedule->id,
                'due_at'      => $schedule->schedule_time,
            ]);
        }

        $verb = $dryRun ? 'Would dispatch' : 'Dispatched';
        $this->info("✅ {$verb} {$dispatched} reminder notification(s).");

        return self::SUCCESS;
    }
}
