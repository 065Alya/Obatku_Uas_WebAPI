<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VAPID Keys for Web Push Notifications
    |--------------------------------------------------------------------------
    |
    | Generate with: npx web-push generate-vapid-keys
    | Then add to your .env:
    |   VAPID_PUBLIC_KEY=your_public_key
    |   VAPID_PRIVATE_KEY=your_private_key
    |   VAPID_SUBJECT=mailto:noreply@obatku.id
    |
    */

    'vapid_public_key'  => env('VAPID_PUBLIC_KEY', ''),
    'vapid_private_key' => env('VAPID_PRIVATE_KEY', ''),
    'vapid_subject'     => env('VAPID_SUBJECT', 'mailto:noreply@obatku.id'),

    /*
    |--------------------------------------------------------------------------
    | PWA App Settings
    |--------------------------------------------------------------------------
    */

    'name'             => env('APP_NAME', 'ObatKu'),
    'short_name'       => 'ObatKu',
    'theme_color'      => '#185FA5',
    'background_color' => '#F8FAFF',
    'display'          => 'standalone',
    'start_url'        => '/dashboard',

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */

    'cache_version'    => env('PWA_CACHE_VERSION', '2.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Push Notification Settings
    |--------------------------------------------------------------------------
    */

    'push_ttl' => 86400, // seconds a push notification lives on push server

    /*
    |--------------------------------------------------------------------------
    | Offline Sync Settings
    |--------------------------------------------------------------------------
    */

    'sync_max_attempts' => 3,
    'sync_retry_delay'  => 60, // seconds between retries
];
