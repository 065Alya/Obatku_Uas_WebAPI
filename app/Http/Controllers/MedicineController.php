<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Services\MedicineService;
use App\Services\ActivityLogService;
use App\Services\OpenFda\DrugLiteracyService;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function __construct(
        protected MedicineService $medicineService,
        protected DrugLiteracyService $literacyService,
    ) {}

    /**
     * Display medicine list.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status']);
        $medicines = $this->medicineService->getUserMedicines($request->user()->id, 15, $filters);
        
        $medicines->appends($filters);

        return view('medicines.index', compact('medicines', 'filters'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $categories = \App\Models\MedicineCategory::orderBy('name')->get();
        $familyMembers = auth()->user()->familyMembers()->get();

        return view('medicines.create', compact('categories', 'familyMembers'));
    }

    /**
     * Store a new medicine.
     */
    public function store(StoreMedicineRequest $request)
    {
        $validated = $request->validated();

        $data = [
            'medicine_name' => $validated['name'],
            'generic_name' => $validated['generic_name'],
            'category_id' => $validated['category_id'],
            'dosage' => $validated['dosage'],
            'unit' => $validated['unit'],
            'form' => $validated['form'],
            'manufacturer' => $validated['manufacturer'],
            'description' => $validated['description'],
            'side_effects' => $validated['side_effects'],
            'stock' => $validated['stock'],
            'stock_alert' => $validated['stock_alert_threshold'],
            'price' => $validated['price'] ?? 0.00,
            'expiry_date' => $validated['expiry_date'],
        ];

        if (!empty($validated['family_member_id'])) {
            $data['owner_type'] = \App\Models\FamilyMember::class;
            $data['owner_id'] = $validated['family_member_id'];
        } else {
            $data['owner_type'] = \App\Models\User::class;
            $data['owner_id'] = $request->user()->id;
        }

        $this->medicineService->createMedicine($data);

        ActivityLogService::log('create', "Menambahkan obat: {$validated['name']}", 'Medicine');

        return redirect()->route('medicines.index')
            ->with('success', 'Obat berhasil ditambahkan!');
    }

    /**
     * Show medicine detail.
     */
    public function show(int $id)
    {
        $medicine = $this->medicineService->getMedicine($id);

        if (!$medicine) {
            abort(404);
        }

        $this->authorize('view', $medicine);

        return view('medicines.show', compact('medicine'));
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        $medicine = $this->medicineService->getMedicine($id);

        if (!$medicine) {
            abort(404);
        }

        $this->authorize('update', $medicine);

        $categories = \App\Models\MedicineCategory::orderBy('name')->get();
        $familyMembers = auth()->user()->familyMembers()->get();

        return view('medicines.edit', compact('medicine', 'categories', 'familyMembers'));
    }

    /**
     * Update medicine.
     */
    public function update(UpdateMedicineRequest $request, int $id)
    {
        $medicine = $this->medicineService->getMedicine($id);

        if (!$medicine) {
            abort(404);
        }

        $this->authorize('update', $medicine);

        $validated = $request->validated();

        $data = [
            'medicine_name' => $validated['name'],
            'generic_name' => $validated['generic_name'],
            'category_id' => $validated['category_id'],
            'dosage' => $validated['dosage'],
            'unit' => $validated['unit'],
            'form' => $validated['form'],
            'manufacturer' => $validated['manufacturer'],
            'description' => $validated['description'],
            'side_effects' => $validated['side_effects'],
            'stock' => $validated['stock'],
            'stock_alert' => $validated['stock_alert_threshold'],
            'price' => $validated['price'] ?? 0.00,
            'expiry_date' => $validated['expiry_date'],
        ];

        if (!empty($validated['family_member_id'])) {
            $data['owner_type'] = \App\Models\FamilyMember::class;
            $data['owner_id'] = $validated['family_member_id'];
        } else {
            $data['owner_type'] = \App\Models\User::class;
            $data['owner_id'] = $request->user()->id;
        }

        $this->medicineService->updateMedicine($id, $data);

        ActivityLogService::log('update', "Mengubah obat: {$validated['name']}", 'Medicine', $id);

        return redirect()->route('medicines.index')
            ->with('success', 'Obat berhasil diperbarui!');
    }

    /**
     * Delete medicine.
     */
    public function destroy(int $id)
    {
        $medicine = $this->medicineService->getMedicine($id);

        if (!$medicine) {
            abort(404);
        }

        $this->authorize('delete', $medicine);

        $name = $medicine->medicine_name;
        $this->medicineService->deleteMedicine($id);

        ActivityLogService::log('delete', "Menghapus obat: {$name}", 'Medicine', $id);

        return redirect()->route('medicines.index')
            ->with('success', 'Obat berhasil dihapus.');
    }

    /**
     * Search medicines (AJAX).
     */
    public function search(Request $request)
    {
        $results = $this->medicineService->searchMedicines(
            $request->user()->id,
            $request->get('q', '')
        );

        return response()->json($results);
    }

    /**
     * Drug Literacy Card — GET /medicines/{id}/literasi
     * Fetches OpenFDA drug label data and presents it in 4 colour-coded categories.
     */
    public function literasi(int $id)
    {
        $medicine = $this->medicineService->getMedicine($id);

        if (!$medicine) {
            abort(404);
        }

        // Access check (same logic as show)
        $isOwner = false;
        if ($medicine->owner_type === \App\Models\User::class && $medicine->owner_id === auth()->id()) {
            $isOwner = true;
        } elseif ($medicine->owner_type === \App\Models\FamilyMember::class) {
            $familyMember = \App\Models\FamilyMember::find($medicine->owner_id);
            if ($familyMember && $familyMember->family && $familyMember->family->user_id === auth()->id()) {
                $isOwner = true;
            }
        }

        if (!$isOwner) {
            abort(403);
        }

        // Try brand name first, fall back to generic name
        $searchName = $medicine->medicine_name;
        $card = $this->literacyService->getCard($searchName);

        // If brand search fails and generic is available, try that
        if (!$card && $medicine->generic_name) {
            $card = $this->literacyService->getCard($medicine->generic_name);
        }

        ActivityLogService::log('view', "Melihat kartu literasi obat: {$medicine->medicine_name}", 'Medicine', $id);

        return view('medicines.literasi', compact('medicine', 'card'));
    }
}
