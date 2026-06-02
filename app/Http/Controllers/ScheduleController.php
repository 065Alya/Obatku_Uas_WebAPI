<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Services\ScheduleService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService,
    ) {}

    /**
     * Display schedule list.
     */
    public function index(Request $request)
    {
        $schedules = $this->scheduleService->getUserSchedules($request->user()->id);

        return view('schedules.index', compact('schedules'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $medicines = auth()->user()->medicines()->where('is_active', true)->orderBy('medicine_name')->get();
        $familyMembers = auth()->user()->familyMembers()->get();

        return view('schedules.create', compact('medicines', 'familyMembers'));
    }

    /**
     * Store a new schedule.
     */
    public function store(StoreScheduleRequest $request)
    {
        $validated = $request->validated();

        $validated['user_id'] = $request->user()->id;

        $this->scheduleService->createSchedule($validated);

        ActivityLogService::log('create', 'Membuat jadwal obat baru', 'MedicineSchedule');

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal obat berhasil dibuat!');
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        $schedule = \App\Models\MedicineSchedule::findOrFail($id);

        $this->authorize('update', $schedule);

        $medicines = auth()->user()->medicines()->where('is_active', true)->orderBy('medicine_name')->get();
        $familyMembers = auth()->user()->familyMembers()->get();

        return view('schedules.edit', compact('schedule', 'medicines', 'familyMembers'));
    }

    /**
     * Update schedule.
     */
    public function update(UpdateScheduleRequest $request, int $id)
    {
        $schedule = \App\Models\MedicineSchedule::findOrFail($id);

        $this->authorize('update', $schedule);

        $validated = $request->validated();

        $this->scheduleService->updateSchedule($id, $validated);

        ActivityLogService::log('update', 'Memperbarui jadwal obat', 'MedicineSchedule', $id);

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui!');
    }

    /**
     * Delete schedule.
     */
    public function destroy(int $id)
    {
        $schedule = \App\Models\MedicineSchedule::findOrFail($id);

        $this->authorize('delete', $schedule);

        $this->scheduleService->deleteSchedule($id);

        ActivityLogService::log('delete', 'Menghapus jadwal obat', 'MedicineSchedule', $id);

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * Log medicine intake.
     */
    public function logIntake(Request $request, int $scheduleId)
    {
        $schedule = \App\Models\MedicineSchedule::findOrFail($scheduleId);

        $this->authorize('update', $schedule);

        $validated = $request->validate([
            'status' => 'required|in:taken,skipped,missed',
            'notes' => 'nullable|string|max:255',
        ]);

        $this->scheduleService->logIntake($scheduleId, $validated['status'], $validated['notes'] ?? null);

        ActivityLogService::log('log_intake', "Mencatat konsumsi obat: {$validated['status']}", 'MedicineSchedule', $scheduleId);

        return back()->with('success', 'Status konsumsi obat berhasil dicatat!');
    }
}
