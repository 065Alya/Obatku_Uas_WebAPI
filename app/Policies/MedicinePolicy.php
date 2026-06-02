<?php

namespace App\Policies;

use App\Models\Medicine;
use App\Models\User;
use App\Models\FamilyMember;

class MedicinePolicy
{
    public function view(User $user, Medicine $medicine): bool
    {
        return $this->isOwner($user, $medicine);
    }

    public function update(User $user, Medicine $medicine): bool
    {
        return $this->isOwner($user, $medicine);
    }

    public function delete(User $user, Medicine $medicine): bool
    {
        return $this->isOwner($user, $medicine);
    }

    private function isOwner(User $user, Medicine $medicine): bool
    {
        if ($medicine->owner_type === User::class && $medicine->owner_id === $user->id) {
            return true;
        }

        if ($medicine->owner_type === FamilyMember::class) {
            $member = FamilyMember::with('family')->find($medicine->owner_id);
            return $member && $member->family && $member->family->user_id === $user->id;
        }

        return false;
    }
}
