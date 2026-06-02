<?php

namespace App\Services\Push\Notifications;

use App\Models\Medicine;
use App\Models\MedicineInteraction;
use App\Notifications\NotificationPayloadBuilder;
use App\Services\Push\PushNotificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * InteractionAlertNotificationService
 *
 * Detects interaction risks from the local medicine_interactions table
 * and dispatches push/SMS alerts to affected users.
 *
 * This is separate from the OpenFDA interaction service — it operates
 * on pharmacist-curated interactions already stored in the DB,
 * triggered when a new medicine is added to a user's cabinet.
 */
final class InteractionAlertNotificationService
{
    public function __construct(
        private readonly PushNotificationService $push,
    ) {}

    /**
     * Check if a newly added medicine interacts with any existing
     * medicines in the user's cabinet and alert if so.
     *
     * @param  int      $userId
     * @param  Medicine $newMedicine  The medicine just added
     * @return int  Number of interaction alerts dispatched
     */
    public function checkAndAlertForUser(int $userId, Medicine $newMedicine): int
    {
        // Get all active medicine IDs in this user's cabinet
        $existingIds = Medicine::where('is_active', true)
            ->where(fn($q) => $q
                ->where(fn($sq) => $sq->where('owner_type', \App\Models\User::class)->where('owner_id', $userId))
                ->orWhere(fn($sq) => $sq->where('owner_type', \App\Models\FamilyMember::class)
                    ->whereIn('owner_id', \App\Models\FamilyMember::whereHas(
                        'family', fn($fq) => $fq->where('user_id', $userId)
                    )->pluck('id')))
            )
            ->where('id', '!=', $newMedicine->id)
            ->pluck('id');

        if ($existingIds->isEmpty()) return 0;

        // Find known interactions with the new medicine
        $interactions = MedicineInteraction::with(['medicineA', 'medicineB'])
            ->where(fn($q) => $q
                ->where(fn($sq) => $sq
                    ->where('medicine_a_id', $newMedicine->id)
                    ->whereIn('medicine_b_id', $existingIds))
                ->orWhere(fn($sq) => $sq
                    ->where('medicine_b_id', $newMedicine->id)
                    ->whereIn('medicine_a_id', $existingIds))
            )
            ->whereIn('severity', ['moderate', 'severe', 'contraindicated'])
            ->get();

        $dispatched = 0;

        foreach ($interactions as $interaction) {
            $drugA = $interaction->medicineA->medicine_name ?? 'Unknown';
            $drugB = $interaction->medicineB->medicine_name ?? 'Unknown';

            // Dedup: one alert per pair per day
            $cacheKey = "push_interaction_{$userId}_{$interaction->id}";
            if (Cache::has($cacheKey)) continue;

            $severity = $this->mapSeverity($interaction->severity);
            $message  = $interaction->description
                ?? "Interaksi {$severity} terdeteksi. {$interaction->recommendation}";

            $payload = NotificationPayloadBuilder::interactionAlert(
                drugA:    $drugA,
                drugB:    $drugB,
                severity: $severity,
                message:  mb_substr($message, 0, 100),
            );

            $this->push->queue($userId, $payload, 'interaction_alert');
            Cache::put($cacheKey, true, now()->addHours(24));
            $dispatched++;

            Log::info('[InteractionAlert] Queued', [
                'user_id'      => $userId,
                'drug_a'       => $drugA,
                'drug_b'       => $drugB,
                'severity'     => $severity,
                'interaction'  => $interaction->id,
            ]);
        }

        return $dispatched;
    }

    /**
     * Dispatch an interaction alert from OpenFDA signal data.
     * Called by OpenFDA interaction check when a 'serious' or 'fatal' signal
     * is found for a user's medicine.
     *
     * @param  int    $userId
     * @param  string $drugA
     * @param  string $drugB
     * @param  string $severity  fatal|serious|moderate|mild
     * @param  string $message
     */
    public function alertFromOpenFda(
        int $userId,
        string $drugA,
        string $drugB,
        string $severity,
        string $message,
    ): void {
        // Only push for meaningful severities
        if (in_array($severity, ['mild', 'unknown', 'none'], true)) return;

        $cacheKey = "push_openfda_interaction_{$userId}_" . md5("{$drugA}{$drugB}");
        if (Cache::has($cacheKey)) return;

        $payload = NotificationPayloadBuilder::interactionAlert(
            drugA:    $drugA,
            drugB:    $drugB,
            severity: $severity,
            message:  mb_substr($message, 0, 100),
        );

        $this->push->queue($userId, $payload, 'interaction_alert');
        Cache::put($cacheKey, now()->addHours(12), now()->addHours(12));

        Log::info('[InteractionAlert][OpenFDA] Queued', compact('userId', 'drugA', 'drugB', 'severity'));
    }

    private function mapSeverity(string $dbSeverity): string
    {
        return match ($dbSeverity) {
            'contraindicated' => 'fatal',
            'severe'          => 'serious',
            'moderate'        => 'moderate',
            default           => 'mild',
        };
    }
}
