<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFamilyMemberRequest;
use App\Http\Requests\UpdateFamilyMemberRequest;
use App\Services\FamilyService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function __construct(
        protected FamilyService $familyService,
    ) {}

    /**
     * Display family members list.
     */
    public function index(Request $request)
    {
        $members = $this->familyService->getUserFamily($request->user()->id);

        return view('family.index', compact('members'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('family.create');
    }

    /**
     * Store a new family member.
     */
    public function store(StoreFamilyMemberRequest $request)
    {
        $validated = $request->validated();

        $family = $request->user()->families()->first();
        if (!$family) {
            $family = $request->user()->families()->create([
                'family_name' => $request->user()->name . "'s Family",
            ]);
        }

        $data = [
            'family_id' => $family->id,
            'name' => $validated['name'],
            'relationship' => $validated['relationship'],
            'birth_date' => $validated['date_of_birth'] ?? null,
            'health_notes' => $validated['health_notes'] ?? null,
        ];

        $this->familyService->createMember($data);

        ActivityLogService::log('create', "Menambahkan anggota keluarga: {$validated['name']}", 'FamilyMember');

        return redirect()->route('family.index')
            ->with('success', 'Anggota keluarga berhasil ditambahkan!');
    }

    /**
     * Show family member detail — PRD Page #8 (TD-06 fix).
     */
    public function show(int $id)
    {
        $member = $this->familyService->getMember($id);

        if (!$member) {
            abort(404);
        }

        $this->authorize('view', $member);

        $medicines = $member->medicines()->with('category')->get();
        $schedules = $member->schedules()->with('medicine')->where('is_active', true)->get();

        // Fetch recent consumptions via medicines belonging to this member
        $medicineIds = $medicines->pluck('id');
        $recentConsumptions = \App\Models\Consumption::whereIn('medicine_id', $medicineIds)
            ->with('medicine')
            ->where('consumed_at', '>=', now()->subDays(7))
            ->orderByDesc('consumed_at')
            ->limit(20)
            ->get();

        // Fallback: also include consumptions tagged with family_member_id directly
        if ($recentConsumptions->isEmpty()) {
            $recentConsumptions = \App\Models\Consumption::where('family_member_id', $member->id)
                ->with('medicine')
                ->where('consumed_at', '>=', now()->subDays(7))
                ->orderByDesc('consumed_at')
                ->limit(20)
                ->get();
        }

        $expiringCount = $medicines->filter(fn($m) => $m->isExpiringSoon(30) && !$m->isExpired())->count();
        $expiredCount  = $medicines->filter(fn($m) => $m->isExpired())->count();

        return view('family.show', compact(
            'member', 'medicines', 'schedules',
            'recentConsumptions', 'expiringCount', 'expiredCount'
        ));
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        $member = $this->familyService->getMember($id);

        if (!$member) {
            abort(404);
        }

        $this->authorize('update', $member);

        return view('family.edit', compact('member'));
    }

    /**
     * Update family member.
     */
    public function update(UpdateFamilyMemberRequest $request, int $id)
    {
        $member = $this->familyService->getMember($id);

        if (!$member) {
            abort(404);
        }

        $this->authorize('update', $member);

        $validated = $request->validated();

        $data = [
            'name' => $validated['name'],
            'relationship' => $validated['relationship'],
            'birth_date' => $validated['date_of_birth'] ?? null,
            'health_notes' => $validated['health_notes'] ?? null,
        ];

        $this->familyService->updateMember($id, $data);

        ActivityLogService::log('update', "Mengubah anggota keluarga: {$validated['name']}", 'FamilyMember', $id);

        return redirect()->route('family.index')
            ->with('success', 'Data anggota keluarga berhasil diperbarui!');
    }

    /**
     * Delete family member.
     */
    public function destroy(int $id)
    {
        $member = $this->familyService->getMember($id);

        if (!$member) {
            abort(404);
        }

        $this->authorize('delete', $member);

        $name = $member->name;
        $this->familyService->deleteMember($id);

        ActivityLogService::log('delete', "Menghapus anggota keluarga: {$name}", 'FamilyMember', $id);

        return redirect()->route('family.index')
            ->with('success', 'Anggota keluarga berhasil dihapus.');
    }
}
