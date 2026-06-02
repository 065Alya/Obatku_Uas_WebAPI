<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\AlertResource;
use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends ApiController
{
    /**
     * GET /api/v1/alerts
     * Paginated alert list for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Alert::where('user_id', $request->user()->id)
            ->orderByDesc('created_at');

        // Optional filter: ?unread=1
        if ($request->boolean('unread')) {
            $query->unread();
        }

        // Optional filter: ?type=stock|interaction|reminder
        if ($request->filled('type')) {
            $query->ofType($request->input('type'));
        }

        $alerts = $query->paginate(20);

        return $this->successResponse(
            AlertResource::collection($alerts)->response()->getData(true)
        );
    }

    /**
     * POST /api/v1/alerts/mark-read
     * Mark ALL unread alerts as read for the authenticated user.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $updated = Alert::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->successResponse(
            ['updated' => $updated],
            "{$updated} notifikasi berhasil ditandai telah dibaca."
        );
    }

    /**
     * PATCH /api/v1/alerts/{id}/read
     * Mark a single alert as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $alert = Alert::find((int) $id);

        if (!$alert) {
            return $this->errorResponse('Notifikasi tidak ditemukan.', 404);
        }

        if ($request->user()->cannot('update', $alert)) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        $alert->markAsRead();

        return $this->successResponse(new AlertResource($alert), 'Notifikasi telah dibaca.');
    }

    /**
     * Convenience: unread count for badge display.
     * GET /api/v1/alerts/unread-count  (if you add this route)
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Alert::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return $this->successResponse(['count' => $count]);
    }

    /**
     * GET /api/v1/stock/alerts
     * Returns all active stock-low and interaction alerts for the user.
     * Includes medicine details where available via the alertable polymorphic relation.
     */
    public function stockAlerts(Request $request): JsonResponse
    {
        $alerts = Alert::where('user_id', $request->user()->id)
            ->whereIn('type', [Alert::TYPE_STOCK, Alert::TYPE_INTERACTION])
            ->unread()
            ->with('alertable')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($a) => [
                'id'            => $a->id,
                'type'          => $a->type,
                'type_label'    => $a->type_label,
                'severity'      => $a->severity,
                'severity_label'=> $a->severity_label,
                'message'       => $a->message,
                'is_read'       => $a->is_read,
                'medicine'      => $a->alertable && $a->alertable instanceof \App\Models\Medicine
                    ? [
                        'id'    => $a->alertable->id,
                        'name'  => $a->alertable->name,
                        'stock' => $a->alertable->stock,
                        'unit'  => $a->alertable->unit,
                    ]
                    : null,
                'created_at'    => $a->created_at->toIso8601String(),
            ]);

        return $this->successResponse([
            'total'        => $alerts->count(),
            'stock_count'  => $alerts->where('type', Alert::TYPE_STOCK)->count(),
            'interaction_count' => $alerts->where('type', Alert::TYPE_INTERACTION)->count(),
            'alerts'       => $alerts->values(),
        ]);
    }
}
