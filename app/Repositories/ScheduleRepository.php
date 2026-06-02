<?php

namespace App\Repositories;

use App\Models\MedicineSchedule;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ScheduleRepository extends BaseRepository implements ScheduleRepositoryInterface
{
    public function __construct(MedicineSchedule $model)
    {
        parent::__construct($model);
    }

    public function getByUser(int $userId, int $perPage = 15)
    {
        return $this->model
            ->where('user_id', $userId)
            ->with(['medicine', 'familyMember', 'logs' => function ($q) {
                $q->whereDate('created_at', now()->toDateString());
            }])
            ->orderBy('schedule_time')
            ->paginate($perPage);
    }

    public function getTodaySchedules(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->where('start_date', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->with(['medicine', 'familyMember', 'logs' => function ($q) {
                $q->whereDate('created_at', now()->toDateString());
            }])
            ->orderBy('schedule_time')
            ->get();
    }

    public function getActiveSchedules(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->with(['medicine', 'familyMember'])
            ->orderBy('schedule_time')
            ->get();
    }

    public function getByMedicine(int $medicineId): Collection
    {
        return $this->model
            ->where('medicine_id', $medicineId)
            ->with('familyMember')
            ->orderBy('schedule_time')
            ->get();
    }

    public function getByFamily(int $familyMemberId): Collection
    {
        return $this->model
            ->where('family_member_id', $familyMemberId)
            ->where('is_active', true)
            ->with('medicine')
            ->orderBy('schedule_time')
            ->get();
    }

    public function getUpcoming(int $userId, int $hours = 2): Collection
    {
        $now = now()->format('H:i:s');
        $later = now()->addHours($hours)->format('H:i:s');

        return $this->model
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->whereBetween('schedule_time', [$now, $later])
            ->with(['medicine', 'familyMember'])
            ->orderBy('schedule_time')
            ->get();
    }
}
