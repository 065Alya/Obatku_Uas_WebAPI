<?php

namespace App\Http\Controllers;

use App\Models\PersonalProfile;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show user profile page.
     */
    public function index(Request $request): View
    {
        $user    = $request->user()->load('profile');
        $profile = $user->profile ?? new PersonalProfile(['user_id' => $user->id]);

        return view('profile.index', compact('user', 'profile'));
    }

    /**
     * Update basic profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'         => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            // Remove old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        ActivityLogService::log('update', 'Memperbarui profil akun.', 'User', $user->id);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update personal profile (health info).
     */
    public function updatePersonalProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'blood_type'       => ['nullable', 'string', 'in:A,B,AB,O,A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'height_cm'        => ['nullable', 'numeric', 'min:50', 'max:300'],
            'weight_kg'        => ['nullable', 'numeric', 'min:1', 'max:500'],
            'allergies'        => ['nullable', 'string', 'max:1000'],
            'chronic_diseases' => ['nullable', 'string', 'max:1000'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        ActivityLogService::log('update', 'Memperbarui data kesehatan profil.', 'PersonalProfile');

        return back()->with('success', 'Data kesehatan berhasil diperbarui!');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLogService::log('update', 'Mengubah kata sandi akun.');

        return back()->with('success', 'Kata sandi berhasil diperbarui!');
    }

    /**
     * GET /profile/settings
     * Notification preferences + accessibility settings (Phase 3, Feature 8).
     */
    public function settings(Request $request): View
    {
        $user = $request->user();

        // Decode stored JSON preferences (stored in user's profile or a dedicated column)
        // We use a session-level default if the user hasn't saved yet.
        $prefs = $this->getPreferences($user);

        return view('profile.settings', compact('user', 'prefs'));
    }

    /**
     * PUT /profile/settings
     * Save notification preferences + accessibility settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notify_stock_alert'        => ['boolean'],
            'notify_expiry_alert'       => ['boolean'],
            'notify_interaction_alert'  => ['boolean'],
            'notify_schedule_reminder'  => ['boolean'],
            'notify_push'               => ['boolean'],
            'notify_sms'                => ['boolean'],
            'accessibility_large_font'  => ['boolean'],
        ]);

        $prefs = [
            'notify_stock_alert'        => (bool) ($validated['notify_stock_alert'] ?? false),
            'notify_expiry_alert'       => (bool) ($validated['notify_expiry_alert'] ?? false),
            'notify_interaction_alert'  => (bool) ($validated['notify_interaction_alert'] ?? false),
            'notify_schedule_reminder'  => (bool) ($validated['notify_schedule_reminder'] ?? false),
            'notify_push'               => (bool) ($validated['notify_push'] ?? false),
            'notify_sms'                => (bool) ($validated['notify_sms'] ?? false),
            'accessibility_large_font'  => (bool) ($validated['accessibility_large_font'] ?? false),
        ];

        // Store as JSON in the PersonalProfile record
        $request->user()->profile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['preferences' => json_encode($prefs)]
        );

        ActivityLogService::log('update', 'Memperbarui preferensi notifikasi dan aksesibilitas.', 'PersonalProfile');

        return back()->with('success', 'Pengaturan berhasil disimpan!');
    }

    /* ── Private Helpers ── */

    private function getPreferences($user): array
    {
        $raw = $user->profile?->preferences ?? null;

        $saved = $raw ? (is_string($raw) ? json_decode($raw, true) : $raw) : [];

        return array_merge([
            'notify_stock_alert'       => true,
            'notify_expiry_alert'      => true,
            'notify_interaction_alert' => true,
            'notify_schedule_reminder' => true,
            'notify_push'              => true,
            'notify_sms'               => false,
            'accessibility_large_font' => false,
        ], (array) $saved);
    }
}
