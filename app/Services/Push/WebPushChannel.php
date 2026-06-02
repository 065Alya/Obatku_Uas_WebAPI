<?php

namespace App\Services\Push;

use App\Models\PushSubscription;
use App\Services\VapidService;
use Illuminate\Support\Facades\Log;

/**
 * WebPushChannel
 *
 * Sends a push notification payload to all active Web Push subscriptions
 * for a given user via the existing VapidService.
 *
 * Returns a ChannelResult with per-subscription outcomes.
 */
final class WebPushChannel
{
    public function __construct(
        private readonly VapidService $vapid,
    ) {}

    /**
     * Send payload to all (or a specific) active subscription for a user.
     *
     * @param  int        $userId
     * @param  array      $payload     Push notification payload array
     * @param  int|null   $subscriptionId  Limit to one device (null = all)
     * @return ChannelResult
     */
    public function send(int $userId, array $payload, ?int $subscriptionId = null): ChannelResult
    {
        $query = PushSubscription::where('user_id', $userId)->active();

        if ($subscriptionId !== null) {
            $query->where('id', $subscriptionId);
        }

        $subscriptions = $query->get();

        if ($subscriptions->isEmpty()) {
            Log::info('[WebPushChannel] No active subscriptions', ['user_id' => $userId]);
            return ChannelResult::noSubscriptions('web_push');
        }

        $sent   = 0;
        $failed = 0;

        foreach ($subscriptions as $sub) {
            $ok = $this->vapid->send($sub, $payload);
            $ok ? $sent++ : $failed++;
        }

        Log::info('[WebPushChannel] Batch complete', [
            'user_id' => $userId,
            'sent'    => $sent,
            'failed'  => $failed,
        ]);

        return new ChannelResult(
            channel:  'web_push',
            sent:     $sent,
            failed:   $failed,
            total:    $subscriptions->count(),
        );
    }
}
