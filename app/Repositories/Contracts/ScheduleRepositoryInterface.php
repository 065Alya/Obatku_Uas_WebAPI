<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ScheduleRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUser(int $userId, int $perPage = 15);

    public function getTodaySchedules(int $userId): Collection;

    public function getActiveSchedules(int $userId): Collection;

    public function getByMedicine(int $medicineId): Collection;

    public function getByFamily(int $familyMemberId): Collection;

    public function getUpcoming(int $userId, int $hours = 2): Collection;
}
