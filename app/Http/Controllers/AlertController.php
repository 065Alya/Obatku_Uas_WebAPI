<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    /**
     * Show all alerts for the authenticated user.
     */
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $alerts = Alert::where('user_id', $userId)
            ->with('alertable')
            ->orderByRaw('is_read ASC')
            ->orderByDesc('created_at')
            ->paginate(20);

        $unreadCount = Alert::where('user_id', $userId)->unread()->count();

        return view('alerts.index', compact('alerts', 'unreadCount'));
    }

    /**
     * Mark a single alert as read.
     */
    public function markRead(Request $request, Alert $alert): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $alert);

        $alert->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi ditandai sebagai telah dibaca.');
    }

    /**
     * Mark all alerts as read for the authenticated user.
     */
    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        Alert::where('user_id', $request->user()->id)
            ->unread()
            ->update(['is_read' => true]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Semua notifikasi ditandai sebagai telah dibaca.']);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Get unread count as JSON (for navbar badge polling).
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Alert::where('user_id', $request->user()->id)->unread()->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Delete (dismiss) an alert.
     */
    public function destroy(Request $request, Alert $alert): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $alert);

        $alert->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi dihapus.');
    }
}
