<?php

namespace App\Notifications;

/**
 * NotificationPayloadBuilder
 *
 * Fluent factory for building structured push notification payloads.
 * Centralises all payload shapes so each notification type has a
 * consistent, icon-rich, action-equipped structure.
 */
final class NotificationPayloadBuilder
{
    /* ─────────────────────────────────────────────────────────────────────
     | Medicine Reminder
     |──────────────────────────────────────────────────────────────────── */

    /**
     * @param string $medicineName  e.g. "Amoxicillin 500mg"
     * @param string $dosageLabel   e.g. "1 kapsul"
     * @param int    $scheduleId
     * @param int    $medicineId
     */
    public static function medicineReminder(
        string $medicineName,
        string $dosageLabel,
        int $scheduleId,
        int $medicineId,
    ): array {
        $cfg = config('notifications.types.medicine_reminder');

        return [
            'title' => '⏰ Pengingat Minum Obat',
            'body'  => "Saatnya minum {$medicineName}" . ($dosageLabel ? " — {$dosageLabel}" : ''),
            'url'   => '/schedules',
            'icon'  => '/icons/icon-192x192.png',
            'badge' => '/icons/icon-72x72.png',
            'tag'   => "medicine-reminder-{$scheduleId}",
            'requireInteraction' => $cfg['require_interaction'],
            'vibrate'            => $cfg['vibrate'],
            'ttl'                => $cfg['ttl'],
            'data' => [
                'type'        => 'medicine_reminder',
                'schedule_id' => $scheduleId,
                'medicine_id' => $medicineId,
                'url'         => '/schedules',
            ],
            'actions' => [
                ['action' => 'open',    'title' => '📋 Buka Jadwal'],
                ['action' => 'dismiss', 'title' => 'Nanti'],
            ],
        ];
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Stock Alert
     |──────────────────────────────────────────────────────────────────── */

    /**
     * @param string $medicineName
     * @param int    $currentStock
     * @param int    $threshold
     * @param int    $medicineId
     */
    public static function stockAlert(
        string $medicineName,
        int $currentStock,
        int $threshold,
        int $medicineId,
    ): array {
        $cfg = config('notifications.types.stock_alert');

        $isOut = $currentStock === 0;
        $title = $isOut ? '🚨 Stok Habis' : '⚠️ Stok Hampir Habis';
        $body  = $isOut
            ? "{$medicineName} sudah habis. Segera beli."
            : "{$medicineName} tinggal {$currentStock} (batas minimum: {$threshold}).";

        return [
            'title'  => $title,
            'body'   => $body,
            'url'    => "/medicines/{$medicineId}",
            'icon'   => '/icons/icon-192x192.png',
            'badge'  => '/icons/icon-72x72.png',
            'tag'    => "stock-alert-{$medicineId}",
            'requireInteraction' => $cfg['require_interaction'],
            'vibrate'            => $cfg['vibrate'],
            'ttl'                => $cfg['ttl'],
            'data' => [
                'type'          => 'stock_alert',
                'medicine_id'   => $medicineId,
                'current_stock' => $currentStock,
                'threshold'     => $threshold,
                'url'           => "/medicines/{$medicineId}",
            ],
            'actions' => [
                ['action' => 'open',    'title' => '💊 Lihat Obat'],
                ['action' => 'dismiss', 'title' => 'Tutup'],
            ],
        ];
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Drug Interaction Alert
     |──────────────────────────────────────────────────────────────────── */

    /**
     * @param string $drugA      First drug name
     * @param string $drugB      Second drug name
     * @param string $severity   mild|moderate|serious|fatal
     * @param string $message    Short interaction description
     */
    public static function interactionAlert(
        string $drugA,
        string $drugB,
        string $severity,
        string $message,
    ): array {
        $cfg = config('notifications.types.interaction_alert');

        $icon = match ($severity) {
            'fatal', 'serious' => '⛔',
            'moderate'         => '⚠️',
            default            => 'ℹ️',
        };

        return [
            'title'  => "{$icon} Peringatan Interaksi Obat",
            'body'   => "{$drugA} + {$drugB}: {$message}",
            'url'    => '/medicines',
            'icon'   => '/icons/icon-192x192.png',
            'badge'  => '/icons/icon-72x72.png',
            'tag'    => 'interaction-alert-' . md5("{$drugA}{$drugB}"),
            'requireInteraction' => $cfg['require_interaction'],
            'vibrate'            => $cfg['vibrate'],
            'ttl'                => $cfg['ttl'],
            'data' => [
                'type'     => 'interaction_alert',
                'drug_a'   => $drugA,
                'drug_b'   => $drugB,
                'severity' => $severity,
                'url'      => '/medicines',
            ],
            'actions' => [
                ['action' => 'open',    'title' => '🔍 Lihat Detail'],
                ['action' => 'dismiss', 'title' => 'Tutup'],
            ],
        ];
    }

    /* ─────────────────────────────────────────────────────────────────────
     | EcoMed Expiry Alert
     |──────────────────────────────────────────────────────────────────── */

    /**
     * @param string $medicineName
     * @param string $expiryDate    formatted date string
     * @param int    $daysLeft      days until expiry (0 = already expired)
     * @param int    $medicineId
     */
    public static function ecomedExpiry(
        string $medicineName,
        string $expiryDate,
        int $daysLeft,
        int $medicineId,
    ): array {
        $cfg = config('notifications.types.ecomed_expiry');

        $isExpired = $daysLeft <= 0;

        $title = $isExpired
            ? '🚨 Obat Kedaluwarsa!'
            : "⏳ Obat Mendekati Kedaluwarsa (H-{$daysLeft})";

        $body = $isExpired
            ? "{$medicineName} sudah kedaluwarsa. Jangan dikonsumsi — buang dengan aman via EcoMed."
            : "{$medicineName} akan kedaluwarsa {$expiryDate}. Segera rencanakan penggunaan atau pembuangan.";

        return [
            'title'  => $title,
            'body'   => $body,
            'url'    => '/ecomed',
            'icon'   => '/icons/icon-192x192.png',
            'badge'  => '/icons/icon-72x72.png',
            'tag'    => "ecomed-expiry-{$medicineId}",
            'requireInteraction' => $cfg['require_interaction'],
            'vibrate'            => $cfg['vibrate'],
            'ttl'                => $cfg['ttl'],
            'data' => [
                'type'        => 'ecomed_expiry',
                'medicine_id' => $medicineId,
                'days_left'   => $daysLeft,
                'expiry_date' => $expiryDate,
                'url'         => '/ecomed',
            ],
            'actions' => [
                ['action' => 'open',    'title' => '♻️ Panduan EcoMed'],
                ['action' => 'dismiss', 'title' => 'Nanti'],
            ],
        ];
    }
}
