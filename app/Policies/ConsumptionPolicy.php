<?php

namespace App\Policies;

use App\Models\Consumption;
use App\Models\User;

class ConsumptionPolicy
{
    public function view(User $user, Consumption $consumption): bool
    {
        return $consumption->user_id === $user->id;
    }

    public function update(User $user, Consumption $consumption): bool
    {
        return $consumption->user_id === $user->id;
    }

    public function delete(User $user, Consumption $consumption): bool
    {
        return $consumption->user_id === $user->id;
    }
}
