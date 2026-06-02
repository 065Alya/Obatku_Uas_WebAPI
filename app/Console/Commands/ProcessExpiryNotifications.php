<?php

namespace App\Console\Commands;

use App\Services\EcoMedService;
use Illuminate\Console\Command;

class ProcessExpiryNotifications extends Command
{
    protected $signature   = 'ecomed:notify-expiry
                              {--user=  : Process only a specific user ID}
                              {--dry-run : Simulate without creating alerts}';

    protected $description = 'Send expiry alerts (H-90/H-30/H-7/expired) for all medicines. Run daily via scheduler.';

    public function __construct(
        protected EcoMedService $ecoMedService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🌿 EcoMed — Processing expiry notifications...');

        if ($this->option('dry-run')) {
            $this->warn('DRY-RUN mode: no alerts will be created.');
        }

        $userId = $this->option('user') ? (int) $this->option('user') : null;

        if ($userId) {
            $this->info("Processing for user ID: {$userId}");
            $dispatched = $this->option('dry-run')
                ? 0
                : $this->ecoMedService->processUserExpiryNotifications($userId);
        } else {
            $this->info('Processing for ALL users...');
            $dispatched = $this->option('dry-run')
                ? 0
                : $this->ecoMedService->processAllExpiryNotifications();
        }

        $this->info("✅ Done. Notifications dispatched: {$dispatched}");

        return self::SUCCESS;
    }
}
