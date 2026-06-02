<?php

namespace App\Http\Controllers;

use App\Models\PersonalProfile;
use App\Services\ActivityLogService;
use App\Services\MedicineService;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PersonalController
 *
 * Handles the Personal Mode dashboard (F-02 PRD).
 * Separate from ProfileController which handles account/security settings.
 */
class PersonalController extends Controller
{
    public function __construct(
        protected MedicineService $medicineService,
        protected ScheduleService $scheduleService,
    ) {}

    /**
     * GET /personal
     * Personal mode dashboard — shows profile summary + active medicines + today's schedules.
     */
    public function index(Request $request): View
    {
        $user    = $request->user()->load('profile');
        $profile = $user->profile ?? new PersonalProfile(['user_id' => $user->id]);

        $userId         = $user->id;
        $todaySchedules = $this->scheduleService->getTodaySchedules($userId);
        $medicines      = $this->medicineService->getUserMedicines($userId, 6);
        $alerts         = $this->medicineService->getAlertSummary($userId);
        $adherenceRate  = $this->scheduleService->getAdherenceRate($userId);

        return view('personal.index', compact(
            'user',
            'profile',
            'todaySchedules',
            'medicines',
            'alerts',
            'adherenceRate',
        ));
    }

    /**
     * GET /personal/edit
     * Edit form for personal profile data (name, medical condition, doctor, emergency contact).
     */
    public function edit(Request $request): View
    {
        $user    = $request->user()->load('profile');
        $profile = $user->profile ?? new PersonalProfile(['user_id' => $user->id]);

        return view('personal.edit', compact('user', 'profile'));
    }

    /**
     * PUT /personal
     * Update personal profile (basic info + health data combined).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:255'],
            'phone'                   => ['nullable', 'string', 'max:20'],
            'date_of_birth'           => ['nullable', 'date', 'before:today'],
            'blood_type'              => ['nullable', 'string', 'in:A,B,AB,O,A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'height_cm'               => ['nullable', 'numeric', 'min:50', 'max:300'],
            'weight_kg'               => ['nullable', 'numeric', 'min:1', 'max:500'],
            'allergies'               => ['nullable', 'string', 'max:1000'],
            'chronic_diseases'        => ['nullable', 'string', 'max:1000'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
        ]);

        // Update user basic fields
        $user->update([
            'name'          => $validated['name'],
            'phone'         => $validated['phone'] ?? $user->phone,
            'date_of_birth' => $validated['date_of_birth'] ?? $user->date_of_birth,
        ]);

        // Upsert health profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'blood_type'              => $validated['blood_type'] ?? null,
                'height_cm'               => $validated['height_cm'] ?? null,
                'weight_kg'               => $validated['weight_kg'] ?? null,
                'allergies'               => $validated['allergies'] ?? null,
                'chronic_diseases'        => $validated['chronic_diseases'] ?? null,
                'emergency_contact_name'  => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            ]
        );

        ActivityLogService::log('update', 'Memperbarui data profil personal.', 'PersonalProfile');

        return redirect()->route('personal.index')
            ->with('success', 'Data profil personal berhasil diperbarui!');
    }
}
