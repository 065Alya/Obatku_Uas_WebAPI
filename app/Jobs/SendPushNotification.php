<?php

namespace App\Jobs;

use App\Models\PushSubscription;
use App\Services\VapidService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendPushNotification
 *
 * Queued job that sends a Web Push notification to one or all active
 * devices of a given user, using the manual VapidService implementation.
 *
 * Usage:
 *   // All active devices
 *   SendPushNotification::dispatch($userId, $payload)->onQueue('notifications');
 *
 *   // Specific subscription
 *   SendPushNotification::dispatch($userId, $payload, $subscriptionId)->onQueue('notifications');
 *
 * Payload shape:
 *   title              string  required
 *   body               string  required
 *   url                string  click destination (default /dashboard)
 *   icon               string  icon URL
 *   badge              string  badge icon URL
 *   tag                string  notification dedup tag
 *   requireInteraction bool
 *   vibrate            int[]   vibration pattern
 *   actions            array   [{action, title}]
 *   data               array   extra data
 */
class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;
    public int $backoff = 60; // seconds between retries

    public function __construct(
        public readonly int   $userId,
        public readonly array $payload,
        public readonly ?int  $subscriptionId = null,
    ) {}

    public function handle(VapidService $vapid): void
    {
        $query = PushSubscription::where('user_id', $this->userId)->active();

        if ($this->subscriptionId) {
            $query->where('id', $this->subscriptionId);
        }

        $subscriptions = $query->get();

        if ($subscriptions->isEmpty()) {
            Log::info('[Push] No active subscriptions for user', ['user_id' => $this->userId]);
            return;
        }

        $successCount = 0;

        foreach ($subscriptions as $subscription) {
            $sent = $vapid->send($subscription, $this->payload);
            if ($sent) {
                $successCount++;
            }
        }

        Log::info('[Push] Notification batch complete', [
            'user_id' => $this->userId,
            'total'   => $subscriptions->count(),
            'success' => $successCount,
        ]);
    }

    /**
     * Handle a job failure — log the error.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[Push] Job permanently failed', [
            'user_id' => $this->userId,
            'error'   => $exception->getMessage(),
        ]);
    }
}
