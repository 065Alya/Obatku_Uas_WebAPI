<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\ScheduleResource;
use App\Models\MedicineSchedule;
use App\Services\ActivityLogService;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends ApiController
{
    public function __construct(protected ScheduleService $scheduleService) {}

    /**
     * GET /api/v1/schedules
     */
    public function index(Request $request): JsonResponse
    {
        $schedules = $this->scheduleService->getUserSchedules($request->user()->id, 20);

        return $this->successResponse(
            ScheduleResource::collection($schedules)->response()->getData(true)
        );
    }

    /**
     * POST /api/v1/schedules
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'medicine_id'      => 'required|exists:medicines,id',
            'family_member_id' => 'nullable|exists:family_members,id',
            'schedule_time'    => 'required|date_format:H:i',
            'frequency'        => 'required|in:daily,twice_daily,three_daily,weekly,monthly,as_needed',
            'dosage_amount'    => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:500',
            'start_date'       => 'required|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['user_id'] = $request->user()->id;

        $schedule = $this->scheduleService->createSchedule($validated);
        ActivityLogService::log('create', 'API: Membuat jadwal obat baru', 'MedicineSchedule', $schedule->id);

        return $this->successResponse(new ScheduleResource($schedule), 'Jadwal berhasil dibuat.', 201);
    }

    /**
     * GET /api/v1/schedules/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $schedule = MedicineSchedule::with(['medicine', 'familyMember', 'logs'])->find((int) $id);

        if (!$schedule) {
            return $this->errorResponse('Jadwal tidak ditemukan.', 404);
        }

        if ($request->user()->cannot('update', $schedule)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        return $this->successResponse(new ScheduleResource($schedule));
    }

    /**
     * PUT /api/v1/schedules/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $schedule = MedicineSchedule::find((int) $id);

        if (!$schedule) {
            return $this->errorResponse('Jadwal tidak ditemukan.', 404);
        }

        if ($request->user()->cannot('update', $schedule)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        $validated = $request->validate([
            'medicine_id'      => 'sometimes|required|exists:medicines,id',
            'family_member_id' => 'nullable|exists:family_members,id',
            'schedule_time'    => 'sometimes|required|date_format:H:i',
            'frequency'        => 'sometimes|required|in:daily,twice_daily,three_daily,weekly,monthly,as_needed',
            'dosage_amount'    => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:500',
            'start_date'       => 'sometimes|required|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'is_active'        => 'sometimes|boolean',
        ]);

        $schedule = $this->scheduleService->updateSchedule((int) $id, $validated);
        ActivityLogService::log('update', 'API: Memperbarui jadwal obat', 'MedicineSchedule', (int) $id);

        return $this->successResponse(new ScheduleResource($schedule), 'Jadwal berhasil diperbarui.');
    }

    /**
     * DELETE /api/v1/schedules/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $schedule = MedicineSchedule::find((int) $id);

        if (!$schedule) {
            return $this->errorResponse('Jadwal tidak ditemukan.', 404);
        }

        if ($request->user()->cannot('delete', $schedule)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        $this->scheduleService->deleteSchedule((int) $id);
        ActivityLogService::log('delete', 'API: Menghapus jadwal obat', 'MedicineSchedule', (int) $id);

        return $this->successResponse(null, 'Jadwal berhasil dihapus.');
    }
}
