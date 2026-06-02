<?php

namespace App\Console\Commands;

use App\Services\Push\Notifications\StockAlertNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * php artisan push:stock-alerts [--user=] [--dry-run]
 *
 * Scans all active medicines for low/zero stock and dispatches push alerts.
 * Scheduled daily at 09:00 WIB via routes/console.php.
 */
class SendStockAlerts extends Command
{
    protected $signature = 'push:stock-alerts
                                {--user=   : Only check for a specific user ID}
                                {--dry-run : Preview without dispatching}';

    protected $description = 'Send push notifications for low/zero medicine stock';

    public function handle(StockAlertNotificationService $service): int
    {
        $userId  = $this->option('user') ? (int) $this->option('user') : null;
        $dryRun  = (bool) $this->option('dry-run');

        $this->info(
            '[push:stock-alerts] Scanning low stock medicines'
            . ($dryRun ? ' [DRY RUN]' : '')
        );

        $count = $service->dispatchLowStockAlerts($userId, $dryRun);

        $verb = $dryRun ? 'Would dispatch' : 'Dispatched';
        $this->info("✅ {$verb} {$count} stock alert(s).");

        Log::info('[push:stock-alerts] Complete', compact('count', 'dryRun'));

        return self::SUCCESS;
    }
}
