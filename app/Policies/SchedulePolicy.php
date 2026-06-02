<?php

namespace App\Policies;

use App\Models\MedicineSchedule;
use App\Models\User;

class SchedulePolicy
{
    public function view(User $user, MedicineSchedule $schedule): bool
    {
        return $this->isOwner($user, $schedule);
    }

    public function update(User $user, MedicineSchedule $schedule): bool
    {
        return $this->isOwner($user, $schedule);
    }

    public function delete(User $user, MedicineSchedule $schedule): bool
    {
        return $this->isOwner($user, $schedule);
    }

    private function isOwner(User $user, MedicineSchedule $schedule): bool
    {
        return $schedule->user_id === $user->id;
    }
}
