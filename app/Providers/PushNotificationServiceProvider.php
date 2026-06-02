<?php

namespace App\Providers;

use App\Services\Push\ChannelResult;
use App\Services\Push\Notifications\EcomedExpiryNotificationService;
use App\Services\Push\Notifications\InteractionAlertNotificationService;
use App\Services\Push\Notifications\MedicineReminderNotificationService;
use App\Services\Push\Notifications\StockAlertNotificationService;
use App\Services\Push\PushNotificationService;
use App\Services\Push\TwilioSmsChannel;
use App\Services\Push\WebPushChannel;
use App\Services\VapidService;
use Illuminate\Support\ServiceProvider;

/**
 * PushNotificationServiceProvider
 *
 * Registers all push notification services as singletons.
 * Register in bootstrap/app.php → withProviders().
 */
class PushNotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Channels ─────────────────────────────────────────────────────
        $this->app->singleton(WebPushChannel::class, fn($app) =>
            new WebPushChannel($app->make(VapidService::class))
        );

        $this->app->singleton(TwilioSmsChannel::class);

        // ── Orchestrator ─────────────────────────────────────────────────
        $this->app->singleton(PushNotificationService::class, fn($app) =>
            new PushNotificationService(
                $app->make(WebPushChannel::class),
                $app->make(TwilioSmsChannel::class),
            )
        );

        // ── Notification-type services ────────────────────────────────────
        $this->app->singleton(MedicineReminderNotificationService::class, fn($app) =>
            new MedicineReminderNotificationService(
                $app->make(PushNotificationService::class)
            )
        );

        $this->app->singleton(StockAlertNotificationService::class, fn($app) =>
            new StockAlertNotificationService(
                $app->make(PushNotificationService::class)
            )
        );

        $this->app->singleton(InteractionAlertNotificationService::class, fn($app) =>
            new InteractionAlertNotificationService(
                $app->make(PushNotificationService::class)
            )
        );

        $this->app->singleton(EcomedExpiryNotificationService::class, fn($app) =>
            new EcomedExpiryNotificationService(
                $app->make(PushNotificationService::class)
            )
        );
    }

    public function boot(): void {}
}
