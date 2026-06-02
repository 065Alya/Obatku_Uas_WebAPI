<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\ConsumptionResource;
use App\Models\Consumption;
use App\Models\MedicineSchedule;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsumptionController extends ApiController
{
    /**
     * GET /api/v1/consumptions/history
     * Adherence history with optional date range.
     * Query params: ?from=2024-01-01&to=2024-01-31&per_page=20
     */
    public function history(Request $request): JsonResponse
    {
        $from    = $request->query('from', now()->subDays(30)->toDateString());
        $to      = $request->query('to', now()->toDateString());
        $perPage = (int) $request->query('per_page', 20);
        $perPage = min($perPage, 100);

        $consumptions = Consumption::where('user_id', $request->user()->id)
            ->with(['medicine', 'schedule'])
            ->whereBetween('consumed_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('consumed_at')
            ->paginate($perPage);

        $adherenceRate = Consumption::adherenceRate($request->user()->id, $from . ' 00:00:00', $to . ' 23:59:59');

        $data = ConsumptionResource::collection($consumptions)->response()->getData(true);
        $data['meta']['adherence_rate'] = $adherenceRate;
        $data['meta']['period']         = ['from' => $from, 'to' => $to];

        return $this->successResponse($data);
    }

    /**
     * GET /api/v1/consumptions
     * List paginated consumption history for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $consumptions = Consumption::where('user_id', $request->user()->id)
            ->with(['medicine', 'schedule'])
            ->orderByDesc('consumed_at')
            ->paginate(20);

        return $this->successResponse(
            ConsumptionResource::collection($consumptions)->response()->getData(true)
        );
    }

    /**
     * POST /api/v1/consumptions
     * Log a medicine consumption event.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'medicine_id'  => 'required|exists:medicines,id',
            'schedule_id'  => 'nullable|exists:medicine_schedules,id',
            'status'       => 'required|in:taken,skipped,missed',
            'dosage_taken' => 'nullable|string|max:100',
            'notes'        => 'nullable|string|max:500',
            'consumed_at'  => 'nullable|date',
        ]);

        $consumption = Consumption::create([
            'user_id'      => $request->user()->id,
            'medicine_id'  => $validated['medicine_id'],
            'schedule_id'  => $validated['schedule_id'] ?? null,
            'status'       => $validated['status'],
            'dosage_taken' => $validated['dosage_taken'] ?? null,
            'notes'        => $validated['notes'] ?? null,
            'consumed_at'  => $validated['consumed_at'] ?? now(),
        ]);

        ActivityLogService::log('create', "API: Mencatat konsumsi obat: {$validated['status']}", 'Consumption', $consumption->id);

        return $this->successResponse(new ConsumptionResource($consumption), 'Konsumsi obat berhasil dicatat.', 201);
    }

    /**
     * GET /api/v1/consumptions/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $consumption = Consumption::with(['medicine', 'schedule'])->find((int) $id);

        if (!$consumption) {
            return $this->errorResponse('Data konsumsi tidak ditemukan.', 404);
        }

        if ($request->user()->cannot('view', $consumption)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        return $this->successResponse(new ConsumptionResource($consumption));
    }

    /**
     * PUT /api/v1/consumptions/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $consumption = Consumption::find((int) $id);

        if (!$consumption) {
            return $this->errorResponse('Data konsumsi tidak ditemukan.', 404);
        }

        if ($request->user()->cannot('update', $consumption)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        $validated = $request->validate([
            'status'       => 'sometimes|required|in:taken,skipped,missed',
            'dosage_taken' => 'nullable|string|max:100',
            'notes'        => 'nullable|string|max:500',
            'consumed_at'  => 'nullable|date',
        ]);

        $consumption->update($validated);

        return $this->successResponse(new ConsumptionResource($consumption), 'Data konsumsi berhasil diperbarui.');
    }

    /**
     * DELETE /api/v1/consumptions/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $consumption = Consumption::find((int) $id);

        if (!$consumption) {
            return $this->errorResponse('Data konsumsi tidak ditemukan.', 404);
        }

        if ($request->user()->cannot('delete', $consumption)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        $consumption->delete();

        return $this->successResponse(null, 'Data konsumsi berhasil dihapus.');
    }
}
