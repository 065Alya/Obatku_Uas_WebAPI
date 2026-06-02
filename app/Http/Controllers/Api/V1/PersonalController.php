<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PersonalProfile;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * V1 Personal Profile API
 *
 * GET  /api/v1/personal  — retrieve the authenticated user's profile + health data
 * PUT  /api/v1/personal  — update personal profile fields
 */
class PersonalController extends ApiController
{
    /**
     * GET /api/v1/personal
     */
    public function show(Request $request): JsonResponse
    {
        $user    = $request->user()->load('profile');
        $profile = $user->profile;

        return $this->successResponse([
            'user' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'phone'         => $user->phone,
                'role'          => $user->role,
                'date_of_birth' => $user->date_of_birth?->toDateString(),
                'avatar_url'    => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'is_active'     => $user->is_active,
            ],
            'profile' => $profile ? [
                'blood_type'              => $profile->blood_type,
                'height_cm'               => $profile->height_cm,
                'weight_kg'               => $profile->weight_kg,
                'bmi'                     => ($profile->height_cm && $profile->weight_kg)
                    ? round($profile->weight_kg / (($profile->height_cm / 100) ** 2), 1)
                    : null,
                'allergies'               => $profile->allergies,
                'chronic_diseases'        => $profile->chronic_diseases,
                'emergency_contact_name'  => $profile->emergency_contact_name,
                'emergency_contact_phone' => $profile->emergency_contact_phone,
            ] : null,
        ]);
    }

    /**
     * PUT /api/v1/personal
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'                    => ['sometimes', 'required', 'string', 'max:255'],
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

        // Update User model fields
        $userFields = array_filter([
            'name'          => $validated['name'] ?? null,
            'phone'         => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
        ], fn($v) => !is_null($v));

        if (!empty($userFields)) {
            $user->update($userFields);
        }

        // Upsert PersonalProfile
        $profileFields = array_filter([
            'blood_type'              => $validated['blood_type'] ?? null,
            'height_cm'               => $validated['height_cm'] ?? null,
            'weight_kg'               => $validated['weight_kg'] ?? null,
            'allergies'               => $validated['allergies'] ?? null,
            'chronic_diseases'        => $validated['chronic_diseases'] ?? null,
            'emergency_contact_name'  => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
        ], fn($v) => !is_null($v));

        if (!empty($profileFields)) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileFields
            );
        }

        ActivityLogService::log('update', 'API: Memperbarui profil personal.', 'PersonalProfile');

        return $this->successResponse(null, 'Profil personal berhasil diperbarui.');
    }
}
