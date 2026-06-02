<?php

namespace App\Policies;

use App\Models\Alert;
use App\Models\User;

class AlertPolicy
{
    public function update(User $user, Alert $alert): bool
    {
        return $alert->user_id === $user->id;
    }

    public function delete(User $user, Alert $alert): bool
    {
        return $alert->user_id === $user->id;
    }
}
