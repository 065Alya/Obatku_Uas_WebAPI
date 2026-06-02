<?php

namespace App\Jobs;

use App\Services\Push\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * PushNotificationJob
 *
 * Queued job that delivers a push notification to a user via
 * PushNotificationService (Web Push primary, Twilio SMS fallback).
 *
 * This job supersedes SendPushNotification for all new notification
 * types while SendPushNotification remains for backward compatibility.
 *
 * Queue: notifications
 * Retry: 3× with exponential backoff [60s, 120s, 300s]
 *
 * Usage:
 *   PushNotificationJob::dispatch($userId, $payload, $type)
 *       ->onQueue('notifications');
 *
 * Or via PushNotificationService::queue() which does the above automatically.
 *
 * Supported types (config: notifications.types):
 *   - medicine_reminder
 *   - stock_alert
 *   - interaction_alert
 *   - ecomed_expiry
 */
class PushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 3;
    public int   $timeout = 30;
    public array $backoff  = [60, 120, 300];

    public function __construct(
        public readonly int    $userId,
        public readonly array  $payload,
        public readonly string $type,
        public readonly ?int   $subscriptionId = null,
    ) {}

    /* ─────────────────────────────────────────────────────────────────────
     | Execution
     |──────────────────────────────────────────────────────────────────── */

    public function handle(PushNotificationService $pushService): void
    {
        $results = $pushService->deliver($this->userId, $this->payload, $this->type);

        $anySuccess = collect($results)->contains(fn($r) => $r->hasSuccess());

        Log::info('[PushNotificationJob] Delivery complete', [
            'user_id'     => $this->userId,
            'type'        => $this->type,
            'any_success' => $anySuccess,
            'channels'    => collect($results)->map->toArray()->all(),
        ]);

        // If no channel succeeded and retries remain, re-throw to trigger retry
        if (!$anySuccess && $this->attempts() < $this->tries) {
            throw new \RuntimeException(
                "[PushNotificationJob] All channels failed for user #{$this->userId} ({$this->type})"
            );
        }
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Failure Handler
     |──────────────────────────────────────────────────────────────────── */

    public function failed(\Throwable $exception): void
    {
        Log::error('[PushNotificationJob] Permanently failed', [
            'user_id' => $this->userId,
            'type'    => $this->type,
            'error'   => $exception->getMessage(),
        ]);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Unique ID — prevents duplicate jobs for same user+type in queue
     |──────────────────────────────────────────────────────────────────── */

    public function uniqueId(): string
    {
        $tag = $this->payload['tag'] ?? $this->type;
        return "push_notif_{$this->userId}_{$tag}";
    }
}
