<?php

namespace App\Services;

use App\Models\ScheduleLog;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ScheduleService
{
    public function __construct(
        protected ScheduleRepositoryInterface $scheduleRepo,
    ) {}

    /**
     * Get paginated schedules for a user.
     */
    public function getUserSchedules(int $userId, int $perPage = 15)
    {
        return $this->scheduleRepo->getByUser($userId, $perPage);
    }

    /**
     * Get today's schedules.
     */
    public function getTodaySchedules(int $userId): Collection
    {
        return $this->scheduleRepo->getTodaySchedules($userId);
    }

    /**
     * Get upcoming schedules within the given hours.
     */
    public function getUpcomingSchedules(int $userId, int $hours = 2): Collection
    {
        return $this->scheduleRepo->getUpcoming($userId, $hours);
    }

    /**
     * Create a new schedule.
     */
    public function createSchedule(array $data): Model
    {
        return $this->scheduleRepo->create($data);
    }

    /**
     * Update a schedule.
     */
    public function updateSchedule(int $id, array $data): Model
    {
        return $this->scheduleRepo->update($id, $data);
    }

    /**
     * Delete a schedule.
     */
    public function deleteSchedule(int $id): bool
    {
        return $this->scheduleRepo->delete($id);
    }

    /**
     * Log a medicine intake (taken, skipped, missed).
     */
    public function logIntake(int $scheduleId, string $status, ?string $notes = null): ScheduleLog
    {
        return ScheduleLog::create([
            'medicine_schedule_id' => $scheduleId,
            'status' => $status,
            'taken_at' => $status === 'taken' ? now() : null,
            'skipped_reason' => $status === 'skipped' ? $notes : null,
            'notes' => $notes,
        ]);
    }

    /**
     * Get adherence rate for a user.
     */
    public function getAdherenceRate(int $userId, int $days = 7): float
    {
        $schedules = $this->scheduleRepo->getActiveSchedules($userId);
        $scheduleIds = $schedules->pluck('id');

        if ($scheduleIds->isEmpty()) {
            return 0;
        }

        $totalLogs = ScheduleLog::whereIn('medicine_schedule_id', $scheduleIds)
            ->where('created_at', '>=', now()->subDays($days))
            ->count();

        $takenLogs = ScheduleLog::whereIn('medicine_schedule_id', $scheduleIds)
            ->where('created_at', '>=', now()->subDays($days))
            ->where('status', 'taken')
            ->count();

        return $totalLogs > 0 ? round(($takenLogs / $totalLogs) * 100, 1) : 0;
    }
}
