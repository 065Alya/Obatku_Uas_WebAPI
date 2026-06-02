<?php

namespace App\Console\Commands;

use App\Services\Push\Notifications\InteractionAlertNotificationService;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * php artisan push:interaction-alerts [--user=] [--dry-run]
 *
 * Scans all users' medicine cabinets for known interactions
 * and dispatches push alerts for unnotified pairs.
 * Scheduled weekly or triggered on-demand.
 */
class SendInteractionAlerts extends Command
{
    protected $signature = 'push:interaction-alerts
                                {--user=   : Only check for a specific user ID}
                                {--dry-run : Preview without dispatching}';

    protected $description = 'Send push notifications for detected drug interactions';

    public function handle(InteractionAlertNotificationService $service): int
    {
        $userId  = $this->option('user') ? (int) $this->option('user') : null;
        $dryRun  = (bool) $this->option('dry-run');

        $this->info(
            '[push:interaction-alerts] Checking interaction alerts'
            . ($dryRun ? ' [DRY RUN]' : '')
        );

        $userIds = $userId
            ? [$userId]
            : User::whereHas('pushSubscriptions', fn($q) => $q->where('is_active', true))
                ->pluck('id')
                ->toArray();

        $total = 0;

        foreach ($userIds as $uid) {
            // For each user, check each medicine against the others
            $medicines = Medicine::where('is_active', true)
                ->where(fn($q) => $q
                    ->where(fn($sq) => $sq->where('owner_type', \App\Models\User::class)->where('owner_id', $uid))
                    ->orWhere(fn($sq) => $sq->where('owner_type', \App\Models\FamilyMember::class)
                        ->whereIn('owner_id', \App\Models\FamilyMember::whereHas(
                            'family', fn($fq) => $fq->where('user_id', $uid)
                        )->pluck('id')))
                )->get();

            foreach ($medicines as $medicine) {
                if ($dryRun) {
                    $this->line("  [DRY RUN] Would check #{$medicine->id} ({$medicine->medicine_name}) for user #{$uid}");
                    continue;
                }

                $count = $service->checkAndAlertForUser($uid, $medicine);
                $total += $count;
            }
        }

        $verb = $dryRun ? 'Would dispatch' : 'Dispatched';
        $this->info("✅ {$verb} {$total} interaction alert(s).");

        Log::info('[push:interaction-alerts] Complete', compact('total', 'dryRun'));

        return self::SUCCESS;
    }
}
