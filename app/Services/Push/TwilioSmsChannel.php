<?php

namespace App\Services\Push;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TwilioSmsChannel
 *
 * Sends an SMS fallback notification via the Twilio REST API.
 * Only active when PUSH_TWILIO_ENABLED=true and the user has a
 * phone number stored on their PersonalProfile.
 *
 * Requires:
 *   TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *   TWILIO_AUTH_TOKEN=your_auth_token
 *   TWILIO_FROM_NUMBER=+628xxxxxxxxxx (or a Twilio Messaging Service SID)
 */
final class TwilioSmsChannel
{
    private bool   $enabled;
    private string $sid;
    private string $token;
    private string $from;

    public function __construct()
    {
        $cfg           = config('notifications.channels.twilio');
        $this->enabled = (bool) $cfg['enabled'];
        $this->sid     = $cfg['sid']   ?? '';
        $this->token   = $cfg['token'] ?? '';
        $this->from    = $cfg['from']  ?? '';
    }

    /**
     * Send an SMS to a specific phone number.
     *
     * @param  string $toNumber  E.164 format (+628xxxxxxx)
     * @param  string $message   Plain-text SMS body (max 160 chars recommended)
     * @return ChannelResult
     */
    public function send(string $toNumber, string $message): ChannelResult
    {
        if (!$this->enabled) {
            return ChannelResult::disabled('twilio');
        }

        if (empty($this->sid) || empty($this->token) || empty($this->from)) {
            Log::warning('[TwilioSmsChannel] Twilio credentials not configured.');
            return ChannelResult::withError('twilio', 'credentials_missing');
        }

        if (empty($toNumber)) {
            return ChannelResult::withError('twilio', 'no_phone_number');
        }

        try {
            $response = Http::withBasicAuth($this->sid, $this->token)
                ->asForm()
                ->post(
                    "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json",
                    [
                        'From' => $this->from,
                        'To'   => $toNumber,
                        'Body' => mb_substr($message, 0, 320), // Twilio limit
                    ]
                );

            if ($response->successful()) {
                Log::info('[TwilioSmsChannel] SMS sent', [
                    'to'     => substr($toNumber, 0, 7) . '***', // mask for logs
                    'sid'    => $response->json('sid'),
                    'status' => $response->json('status'),
                ]);

                return new ChannelResult(
                    channel: 'twilio',
                    sent:    1,
                    failed:  0,
                    total:   1,
                );
            }

            $errorMsg = $response->json('message', 'Twilio error');
            Log::warning('[TwilioSmsChannel] SMS failed', [
                'status' => $response->status(),
                'error'  => $errorMsg,
            ]);

            return ChannelResult::withError('twilio', $errorMsg);

        } catch (\Throwable $e) {
            Log::error('[TwilioSmsChannel] Exception', ['error' => $e->getMessage()]);
            return ChannelResult::withError('twilio', $e->getMessage());
        }
    }

    /**
     * Build a plain-text SMS body from a push payload.
     * Strips emojis in favour of plain text for SMS compatibility.
     */
    public static function buildSmsBody(array $pushPayload): string
    {
        $title = preg_replace('/[\x{1F300}-\x{1FFFF}]/u', '', $pushPayload['title'] ?? '');
        $body  = $pushPayload['body'] ?? '';

        return trim("{$title}\n{$body}\n\nObatKu — obatku.id");
    }
}
