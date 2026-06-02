<?php

namespace App\Observers;

use App\Models\Alert;
use App\Models\FamilyMember;
use App\Models\Medicine;
use App\Models\User;
use App\Services\OpenFda\DrugInteractionService;
use Illuminate\Support\Facades\Log;

/**
 * MedicineObserver
 *
 * Automatically scans for drug interactions whenever a medicine is created or its
 * name fields change. Triggers the OpenFDA adverse-event analysis and creates
 * an in-app Alert if a potential interaction is detected (PRD F-05).
 *
 * Kept non-blocking: all exceptions are caught and logged, never propagated.
 */
class MedicineObserver
{
    public function __construct(
        private readonly DrugInteractionService $interactionService,
    ) {}

    /* ─────────────────────────────────────────────────────────────────────
     | Eloquent Hooks
     |──────────────────────────────────────────────────────────────────── */

    public function created(Medicine $medicine): void
    {
        $this->checkInteractions($medicine);
    }

    public function updated(Medicine $medicine): void
    {
        // Only re-scan when the drug name actually changed — avoids redundant API calls
        if ($medicine->wasChanged(['medicine_name', 'generic_name'])) {
            $this->checkInteractions($medicine);
        }
    }

    /* ─────────────────────────────────────────────────────────────────────
     | Private Helpers
     |──────────────────────────────────────────────────────────────────── */

    private function checkInteractions(Medicine $medicine): void
    {
        try {
            $userId = $this->resolveUserId($medicine);
            if (!$userId) {
                return;
            }

            // Resolve all other active medicine names for this user + family
            $familyMemberIds = FamilyMember::whereHas('family', fn($q) => $q->where('user_id', $userId))
                ->pluck('id');

            $otherMedicines = Medicine::where('is_active', true)
                ->where('id', '!=', $medicine->id)
                ->where(function ($q) use ($userId, $familyMemberIds) {
                    $q->where(fn($sq) => $sq->where('owner_type', User::class)->where('owner_id', $userId))
                      ->orWhere(fn($sq) => $sq->where('owner_type', FamilyMember::class)->whereIn('owner_id', $familyMemberIds));
                })
                ->get();

            if ($otherMedicines->isEmpty()) {
                return;
            }

            $newName = $medicine->medicine_name;

            foreach ($otherMedicines as $other) {
                $otherName = $other->medicine_name;

                // Skip if we have already alerted for this pair in the last 7 days
                $alreadyAlerted = Alert::where('user_id', $userId)
                    ->where('type', Alert::TYPE_INTERACTION)
                    ->where('message', 'like', "%{$newName}%")
                    ->where('message', 'like', "%{$otherName}%")
                    ->where('created_at', '>=', now()->subDays(7))
                    ->exists();

                if ($alreadyAlerted) {
                    continue;
                }

                $result = $this->interactionService->quickCheck($newName, $otherName);

                if ($result['has_interaction']) {
                    Alert::createInteractionAlert(
                        $userId,
                        $medicine,
                        $other,
                        $result['message']
                    );

                    Log::info('[MedicineObserver] Interaction alert created', [
                        'user_id'  => $userId,
                        'drug_a'   => $newName,
                        'drug_b'   => $otherName,
                        'severity' => $result['severity'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Non-blocking — never fail a medicine save because of a failed API call
            Log::warning('[MedicineObserver] Interaction check failed', [
                'medicine_id' => $medicine->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    private function resolveUserId(Medicine $medicine): ?int
    {
        if ($medicine->owner_type === User::class) {
            return $medicine->owner_id;
        }

        if ($medicine->owner_type === FamilyMember::class) {
            $member = FamilyMember::with('family')->find($medicine->owner_id);
            return $member?->family?->user_id;
        }

        return null;
    }
}
