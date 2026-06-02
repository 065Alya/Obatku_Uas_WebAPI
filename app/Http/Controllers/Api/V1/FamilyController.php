<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\FamilyMember;
use App\Services\ActivityLogService;
use App\Services\FamilyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * V1 Family Members API
 *
 * GET  /api/v1/families                 — list user's family members
 * POST /api/v1/families/members         — create a new family member
 * PUT  /api/v1/families/members/{id}    — update a family member
 */
class FamilyController extends ApiController
{
    public function __construct(protected FamilyService $familyService) {}

    /**
     * GET /api/v1/families
     * Returns all family members belonging to the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $members = $this->familyService->getUserFamily($request->user()->id);

        $data = $members->map(fn($m) => [
            'id'             => $m->id,
            'family_id'      => $m->family_id,
            'name'           => $m->name,
            'relationship'   => $m->relationship,
            'birth_date'     => $m->birth_date?->toDateString(),
            'age'            => $m->age,
            'health_notes'   => $m->health_notes,
            'medicines_count'=> $m->medicines()->count(),
            'schedules_count'=> $m->schedules()->count(),
        ]);

        return $this->successResponse($data);
    }

    /**
     * POST /api/v1/families/members
     * Create a new family member.  Automatically creates a Family record for
     * the user if one does not yet exist.
     */
    public function storeMember(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'relationship'  => ['required', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'health_notes'  => ['nullable', 'string', 'max:1000'],
        ]);

        $user   = $request->user();
        $family = $user->families()->first()
                ?? $user->families()->create(['family_name' => $user->name . "'s Family"]);

        $member = $this->familyService->createMember([
            'family_id'    => $family->id,
            'name'         => $validated['name'],
            'relationship' => $validated['relationship'],
            'birth_date'   => $validated['date_of_birth'] ?? null,
            'health_notes' => $validated['health_notes'] ?? null,
        ]);

        ActivityLogService::log(
            'create',
            "API: Menambahkan anggota keluarga: {$validated['name']}",
            'FamilyMember',
            $member->id
        );

        return $this->successResponse([
            'id'           => $member->id,
            'family_id'    => $member->family_id,
            'name'         => $member->name,
            'relationship' => $member->relationship,
            'birth_date'   => $member->birth_date?->toDateString(),
            'health_notes' => $member->health_notes,
        ], 'Anggota keluarga berhasil ditambahkan.', 201);
    }

    /**
     * PUT /api/v1/families/members/{id}
     * Update an existing family member (owner check enforced).
     */
    public function updateMember(Request $request, string $id): JsonResponse
    {
        $member = FamilyMember::with('family')->find((int) $id);

        if (!$member) {
            return $this->errorResponse('Anggota keluarga tidak ditemukan.', 404);
        }

        if ($request->user()->cannot('update', $member)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        $validated = $request->validate([
            'name'          => ['sometimes', 'required', 'string', 'max:255'],
            'relationship'  => ['sometimes', 'required', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'health_notes'  => ['nullable', 'string', 'max:1000'],
        ]);

        $data = array_filter([
            'name'         => $validated['name'] ?? null,
            'relationship' => $validated['relationship'] ?? null,
            'birth_date'   => $validated['date_of_birth'] ?? null,
            'health_notes' => $validated['health_notes'] ?? null,
        ], fn($v) => !is_null($v));

        $member = $this->familyService->updateMember((int) $id, $data);

        ActivityLogService::log(
            'update',
            "API: Memperbarui anggota keluarga: {$member->name}",
            'FamilyMember',
            $member->id
        );

        return $this->successResponse([
            'id'           => $member->id,
            'name'         => $member->name,
            'relationship' => $member->relationship,
            'birth_date'   => $member->birth_date?->toDateString(),
            'health_notes' => $member->health_notes,
        ], 'Data anggota keluarga berhasil diperbarui.');
    }
}
