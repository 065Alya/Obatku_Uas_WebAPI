<?php

namespace App\Services\Push;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * PushNotificationService
 *
 * Central orchestrator for all push notifications in ObatKu.
 * Determines which channels to use per notification type, executes
 * primary (Web Push) then falls back to Twilio SMS when configured.
 *
 * All actual delivery is async — this service dispatches Jobs.
 * For synchronous delivery, call send() directly without a queue.
 */
final class PushNotificationService
{
    public function __construct(
        private readonly WebPushChannel   $webPush,
        private readonly TwilioSmsChannel $twilio,
    ) {}

    /* ─────────────────────────────────────────────────────────────────────
     | Primary Dispatch — synchronous (used inside Jobs)
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Deliver a notification to a user across all configured channels.
     *
     * @param  int    $userId
     * @param  array  $payload        Push payload from NotificationPayloadBuilder
     * @param  string $type           Notification type key (config: notifications.types.*)
     * @return ChannelResult[]  Indexed by channel name
     */
    public function deliver(int $userId, array $payload, string $type): array
    {
        $channelKeys = config("notifications.types.{$type}.channels", ['web_push']);
        $results     = [];

        foreach ($channelKeys as $channel) {
            $result = match ($channel) {
                'web_push' => $this->sendWebPush($userId, $payload),
                'twilio'   => $this->sendTwilio($userId, $payload),
                default    => ChannelResult::disabled($channel),
            };

            $results[$channel] = $result;

            // Stop iterating when a channel succeeds (prevents SMS if push works)
            if ($result->hasSuccess()) {
                break;
            }

            Log::info("[PushNotificationService] Channel '{$channel}' did not deliver, trying next.", [
                'user_id' => $userId,
                'type'    => $type,
                'result'  => $result->toArray(),
            ]);
        }

        return $results;
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Queue-based Dispatch — fire-and-forget (used by Commands)
     |──────────────────────────────────────────────────────────────────── */

    /**
     * Dispatch the PushNotificationJob onto the configured queue.
     * This is the preferred method for scheduler-driven notifications.
     *
     * @param  int    $userId
     * @param  array  $payload
     * @param  string $type
     */
    public function queue(int $userId, array $payload, string $type): void
    {
        \App\Jobs\PushNotificationJob::dispatch($userId, $payload, $type)
            ->onQueue(config('notifications.queue.name', 'notifications'));
    }

    /**
     * Queue a notification for multiple users at once.
     *
     * @param  int[]  $userIds
     * @param  array  $payload
     * @param  string $type
     */
    public function queueBatch(array $userIds, array $payload, string $type): void
    {
        foreach ($userIds as $userId) {
            $this->queue($userId, $payload, $type);
        }
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Private — Channel Wrappers
     |──────────────────────────────────────────────────────────────────── */

    private function sendWebPush(int $userId, array $payload): ChannelResult
    {
        if (!config('notifications.channels.web_push.enabled', true)) {
            return ChannelResult::disabled('web_push');
        }

        return $this->webPush->send($userId, $payload);
    }

    private function sendTwilio(int $userId, array $payload): ChannelResult
    {
        if (!config('notifications.channels.twilio.enabled', false)) {
            return ChannelResult::disabled('twilio');
        }

        // Resolve the user's phone number from PersonalProfile
        $phone = $this->resolvePhoneNumber($userId);

        if (!$phone) {
            Log::info('[PushNotificationService] No phone number for Twilio fallback', ['user_id' => $userId]);
            return ChannelResult::withError('twilio', 'no_phone_number');
        }

        $smsBody = TwilioSmsChannel::buildSmsBody($payload);

        return $this->twilio->send($phone, $smsBody);
    }

    /**
     * Resolve a user's phone number from their personal profile.
     * Returns null if not set or profile doesn't exist.
     */
    private function resolvePhoneNumber(int $userId): ?string
    {
        $user = User::with('personalProfile')->find($userId);

        return $user?->personalProfile?->phone ?? null;
    }
}
