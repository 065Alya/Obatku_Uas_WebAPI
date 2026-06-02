<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\MedicineResource;
use App\Models\FamilyMember;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\MedicineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicineController extends ApiController
{
    public function __construct(protected MedicineService $medicineService) {}

    /**
     * GET /api/v1/medicines
     */
    public function index(Request $request): JsonResponse
    {
        $medicines = $this->medicineService->getUserMedicines($request->user()->id, 20);

        return $this->successResponse(
            MedicineResource::collection($medicines)->response()->getData(true)
        );
    }

    /**
     * POST /api/v1/medicines
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'generic_name'         => 'nullable|string|max:255',
            'category_id'          => 'nullable|exists:medicine_categories,id',
            'family_member_id'     => 'nullable|exists:family_members,id',
            'dosage'               => 'nullable|string|max:100',
            'unit'                 => 'required|string|max:50',
            'form'                 => 'required|string|max:50',
            'manufacturer'         => 'nullable|string|max:255',
            'description'          => 'nullable|string',
            'side_effects'         => 'nullable|string',
            'stock'                => 'required|integer|min:0',
            'stock_alert_threshold'=> 'required|integer|min:0',
            'price'                => 'nullable|numeric|min:0',
            'expiry_date'          => 'nullable|date|after:today',
        ]);

        $data = [
            'medicine_name' => $validated['name'],
            'generic_name'  => $validated['generic_name'] ?? null,
            'category_id'   => $validated['category_id'] ?? null,
            'dosage'        => $validated['dosage'] ?? null,
            'unit'          => $validated['unit'],
            'form'          => $validated['form'],
            'manufacturer'  => $validated['manufacturer'] ?? null,
            'description'   => $validated['description'] ?? null,
            'side_effects'  => $validated['side_effects'] ?? null,
            'stock'         => $validated['stock'],
            'stock_alert'   => $validated['stock_alert_threshold'],
            'price'         => $validated['price'] ?? 0,
            'expiry_date'   => $validated['expiry_date'] ?? null,
        ];

        if (!empty($validated['family_member_id'])) {
            $data['owner_type'] = FamilyMember::class;
            $data['owner_id']   = $validated['family_member_id'];
        } else {
            $data['owner_type'] = User::class;
            $data['owner_id']   = $request->user()->id;
        }

        $medicine = $this->medicineService->createMedicine($data);
        ActivityLogService::log('create', "API: Menambahkan obat: {$validated['name']}", 'Medicine', $medicine->id);

        return $this->successResponse(new MedicineResource($medicine), 'Obat berhasil ditambahkan.', 201);
    }

    /**
     * GET /api/v1/medicines/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $medicine = $this->medicineService->getMedicine((int) $id);

        if (!$medicine) {
            return $this->errorResponse('Obat tidak ditemukan.', 404);
        }

        if ($request->user()->cannot('view', $medicine)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        return $this->successResponse(new MedicineResource($medicine));
    }

    /**
     * PUT /api/v1/medicines/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $medicine = $this->medicineService->getMedicine((int) $id);

        if (!$medicine) {
            return $this->errorResponse('Obat tidak ditemukan.', 404);
        }

        if ($request->user()->cannot('update', $medicine)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        $validated = $request->validate([
            'name'                 => 'sometimes|required|string|max:255',
            'generic_name'         => 'nullable|string|max:255',
            'category_id'          => 'nullable|exists:medicine_categories,id',
            'dosage'               => 'nullable|string|max:100',
            'unit'                 => 'sometimes|required|string|max:50',
            'form'                 => 'sometimes|required|string|max:50',
            'manufacturer'         => 'nullable|string|max:255',
            'description'          => 'nullable|string',
            'side_effects'         => 'nullable|string',
            'stock'                => 'sometimes|required|integer|min:0',
            'stock_alert_threshold'=> 'sometimes|required|integer|min:0',
            'price'                => 'nullable|numeric|min:0',
            'expiry_date'          => 'nullable|date',
        ]);

        $data = array_filter([
            'medicine_name' => $validated['name'] ?? null,
            'generic_name'  => $validated['generic_name'] ?? null,
            'category_id'   => $validated['category_id'] ?? null,
            'dosage'        => $validated['dosage'] ?? null,
            'unit'          => $validated['unit'] ?? null,
            'form'          => $validated['form'] ?? null,
            'manufacturer'  => $validated['manufacturer'] ?? null,
            'description'   => $validated['description'] ?? null,
            'side_effects'  => $validated['side_effects'] ?? null,
            'stock'         => $validated['stock'] ?? null,
            'stock_alert'   => $validated['stock_alert_threshold'] ?? null,
            'price'         => $validated['price'] ?? null,
            'expiry_date'   => $validated['expiry_date'] ?? null,
        ], fn($v) => !is_null($v));

        $medicine = $this->medicineService->updateMedicine((int) $id, $data);
        ActivityLogService::log('update', "API: Mengubah obat: {$medicine->medicine_name}", 'Medicine', $medicine->id);

        return $this->successResponse(new MedicineResource($medicine), 'Obat berhasil diperbarui.');
    }

    /**
     * DELETE /api/v1/medicines/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $medicine = $this->medicineService->getMedicine((int) $id);

        if (!$medicine) {
            return $this->errorResponse('Obat tidak ditemukan.', 404);
        }

        if ($request->user()->cannot('delete', $medicine)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        $name = $medicine->medicine_name;
        $this->medicineService->deleteMedicine((int) $id);
        ActivityLogService::log('delete', "API: Menghapus obat: {$name}", 'Medicine', (int) $id);

        return $this->successResponse(null, 'Obat berhasil dihapus.');
    }

}
