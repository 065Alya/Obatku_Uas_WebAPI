<?php

namespace App\Policies;

use App\Models\FamilyMember;
use App\Models\User;

class FamilyMemberPolicy
{
    public function view(User $user, FamilyMember $member): bool
    {
        return $this->isOwner($user, $member);
    }

    public function update(User $user, FamilyMember $member): bool
    {
        return $this->isOwner($user, $member);
    }

    public function delete(User $user, FamilyMember $member): bool
    {
        return $this->isOwner($user, $member);
    }

    private function isOwner(User $user, FamilyMember $member): bool
    {
        return $member->family && $member->family->user_id === $user->id;
    }
}
