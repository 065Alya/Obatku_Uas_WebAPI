<?php

use App\Console\Commands\ProcessExpiryNotifications;
use App\Console\Commands\SendInteractionAlerts;
use App\Console\Commands\SendMedicineReminders;
use App\Console\Commands\SendStockAlerts;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| ObatKu Scheduled Commands
|--------------------------------------------------------------------------
|
| All scheduler entries use Asia/Jakarta (WIB, UTC+7) timezone and
| withoutOverlapping() to prevent concurrent runs on busy servers.
|
| Run the scheduler: php artisan schedule:run (via cron every minute)
|
*/

// ── EcoMed: Daily expiry notifications ─────────────────────────────────────
// Sends push notifications for medicines expiring within 7 / 30 / 90 days.
Schedule::command('ecomed:notify-expiry')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('[Scheduler] ecomed:notify-expiry failed.');
    });

// ── PWA: Medicine reminder push notifications ───────────────────────────────
// Runs every 15 minutes to dispatch push reminders for upcoming doses.
// Uses a 15-minute look-ahead window relative to scheduled_time in WIB.
Schedule::command('pwa:send-medicine-reminders')
    ->everyFifteenMinutes()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->between('06:00', '23:00')  // only run during waking hours
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('[Scheduler] pwa:send-medicine-reminders failed.');
    });

// ── Push: Stock alerts ──────────────────────────────────────────────────────
// Check all user medicine cabinets for low/zero stock once daily.
Schedule::command('push:stock-alerts')
    ->dailyAt(config('notifications.schedule.stock_check_time', '09:00'))
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('[Scheduler] push:stock-alerts failed.');
    });

// ── Push: Interaction alerts ────────────────────────────────────────────────
// Weekly scan of all medicine cabinets for known drug interaction pairs.
// Runs Sunday at 10:00 WIB — lighter load day.
if (config('notifications.schedule.interaction_check_enabled', true)) {
    Schedule::command('push:interaction-alerts')
        ->weekly()
        ->sundays()
        ->at('10:00')
        ->timezone('Asia/Jakarta')
        ->withoutOverlapping()
        ->runInBackground()
        ->onFailure(function () {
            \Illuminate\Support\Facades\Log::error('[Scheduler] push:interaction-alerts failed.');
        });
}

// ── Queue Worker: Process offline sync items ────────────────────────────────
// Retries failed sync queue items that are eligible for retry.
// Complements the service worker background sync with a server-side check.
Schedule::command('queue:retry', ['all'])
    ->hourly()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::warning('[Scheduler] queue:retry failed.');
    });
