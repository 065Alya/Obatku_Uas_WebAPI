<?php

namespace App\Services\Push;

/**
 * ChannelResult
 *
 * Value object returned by each push channel after an attempt.
 * Used by PushNotificationService to aggregate outcomes and decide fallback.
 */
final class ChannelResult
{
    public function __construct(
        public readonly string $channel,
        public readonly int    $sent,
        public readonly int    $failed,
        public readonly int    $total,
        public readonly ?string $error = null,
    ) {}

    /** True when at least one push was delivered. */
    public function hasSuccess(): bool
    {
        return $this->sent > 0;
    }

    /** True when every attempt failed (total > 0 but sent = 0). */
    public function allFailed(): bool
    {
        return $this->total > 0 && $this->sent === 0;
    }

    /** True when user had no subscriptions on this channel. */
    public function isEmpty(): bool
    {
        return $this->total === 0;
    }

    /** Named constructor — user has no subscriptions. */
    public static function noSubscriptions(string $channel): self
    {
        return new self(
            channel: $channel,
            sent:    0,
            failed:  0,
            total:   0,
        );
    }

    /** Named constructor — channel is disabled in config. */
    public static function disabled(string $channel): self
    {
        return new self(
            channel: $channel,
            sent:    0,
            failed:  0,
            total:   0,
            error:   'channel_disabled',
        );
    }

    /** Named constructor — delivery error. */
    public static function withError(string $channel, string $message): self
    {
        return new self(
            channel: $channel,
            sent:    0,
            failed:  1,
            total:   1,
            error:   $message,
        );
    }

    public function toArray(): array
    {
        return [
            'channel' => $this->channel,
            'sent'    => $this->sent,
            'failed'  => $this->failed,
            'total'   => $this->total,
            'error'   => $this->error,
        ];
    }
}
