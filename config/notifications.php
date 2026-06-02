<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Push Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Centralised config for the ObatKu push notification system.
    | Covers Web Push (VAPID), Twilio SMS fallback, queue settings,
    | and per-channel notification type preferences.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Channels
    |--------------------------------------------------------------------------
    | Primary channel: web_push (VAPID via VapidService)
    | Fallback channel: twilio (SMS via Twilio REST API)
    */

    'channels' => [
        'web_push' => [
            'enabled' => env('PUSH_WEB_PUSH_ENABLED', true),
        ],
        'twilio' => [
            'enabled'   => env('PUSH_TWILIO_ENABLED', false),
            'sid'       => env('TWILIO_ACCOUNT_SID', ''),
            'token'     => env('TWILIO_AUTH_TOKEN', ''),
            'from'      => env('TWILIO_FROM_NUMBER', ''),
            // Twilio Notify service SID (optional — for broadcast)
            'notify_sid'=> env('TWILIO_NOTIFY_SERVICE_SID', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    */

    'queue' => [
        // All push notification jobs run on this queue
        'name'    => env('PUSH_QUEUE_NAME', 'notifications'),
        'tries'   => 3,
        'backoff' => [60, 120, 300], // seconds between retries (1m, 2m, 5m)
        'timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Types
    |--------------------------------------------------------------------------
    | Controls which channels each notification type uses.
    | Order matters — web_push is tried first, twilio as fallback.
    */

    'types' => [
        'medicine_reminder' => [
            'channels' => ['web_push', 'twilio'],
            'require_interaction' => true,
            'vibrate'  => [300, 100, 300, 100, 300],
            'ttl'      => 900,   // 15 minutes — reminder is useless after this
        ],
        'stock_alert' => [
            'channels' => ['web_push'],
            'require_interaction' => false,
            'vibrate'  => [200, 100, 200],
            'ttl'      => 86400, // 24 hours
        ],
        'interaction_alert' => [
            'channels' => ['web_push', 'twilio'],
            'require_interaction' => true,
            'vibrate'  => [500, 100, 500, 100, 500],
            'ttl'      => 3600,  // 1 hour
        ],
        'ecomed_expiry' => [
            'channels' => ['web_push'],
            'require_interaction' => false,
            'vibrate'  => [200, 100, 200],
            'ttl'      => 86400,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Stock Alert Thresholds
    |--------------------------------------------------------------------------
    */

    'stock' => [
        // Re-send stock alert after this many days even if already notified
        'resend_after_days' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduler Windows (WIB / Asia/Jakarta)
    |--------------------------------------------------------------------------
    */

    'schedule' => [
        'reminder_window_minutes'   => env('PUSH_REMINDER_WINDOW', 15),
        'stock_check_time'          => '09:00',
        'interaction_check_enabled' => true,
    ],

];
