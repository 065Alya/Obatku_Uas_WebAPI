<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PwaController extends Controller
{
    /**
     * Serve the PWA manifest dynamically (allows env-driven values).
     */
    public function manifest(): \Illuminate\Http\Response
    {
        $manifest = [
            'name'             => config('app.name', 'ObatKu') . ' — Manajemen Obat Keluarga',
            'short_name'       => 'ObatKu',
            'description'      => 'Aplikasi manajemen obat keluarga untuk Indonesia.',
            'start_url'        => '/dashboard',
            'scope'            => '/',
            'display'          => 'standalone',
            'orientation'      => 'portrait-primary',
            'theme_color'      => '#185FA5',
            'background_color' => '#F8FAFF',
            'lang'             => 'id',
            'dir'              => 'ltr',
            'icons'            => $this->iconList(),
            'shortcuts'        => [
                ['name' => 'Dashboard',    'url' => '/dashboard',       'icons' => [['src' => '/icons/icon-96x96.png', 'sizes' => '96x96']]],
                ['name' => 'Jadwal Obat',  'url' => '/schedules',       'icons' => [['src' => '/icons/icon-96x96.png', 'sizes' => '96x96']]],
                ['name' => 'Tambah Obat',  'url' => '/medicines/create','icons' => [['src' => '/icons/icon-96x96.png', 'sizes' => '96x96']]],
                ['name' => 'EcoMed',       'url' => '/ecomed',          'icons' => [['src' => '/icons/icon-96x96.png', 'sizes' => '96x96']]],
            ],
        ];

        return response(json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Serve the service worker from public/sw.js with correct headers.
     *
     * The SW MUST be served from the root scope with no-cache headers.
     * We replace the __SW_VERSION__ placeholder with the configured
     * PWA_CACHE_VERSION at serve time so cache names can be busted
     * by bumping a single env variable without editing sw.js.
     */
    public function serviceWorker(): \Illuminate\Http\Response
    {
        $swPath = public_path('sw.js');

        abort_unless(file_exists($swPath), 404);

        $version = config('pwa.cache_version', '2.0.0');
        $content = str_replace(
            '__SW_VERSION__',
            $version,
            file_get_contents($swPath)
        );

        return response($content)
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Register (or update) a push subscription for the authenticated user.
     *
     * POST /api/pwa/push-subscribe
     */
    public function pushSubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint'    => 'required|string|url',
            'p256dh_key'  => 'required|string',
            'auth_token'  => 'required|string',
            'device_name' => 'nullable|string|max:100',
            'user_agent'  => 'nullable|string|max:512',
        ]);

        $subscription = PushSubscription::upsertForUser($request->user()->id, $validated);

        return response()->json([
            'message' => 'Push subscription berhasil disimpan.',
            'id'      => $subscription->id,
        ], 201);
    }

    /**
     * Remove a push subscription for the authenticated user.
     *
     * POST /api/pwa/push-unsubscribe
     */
    public function pushUnsubscribe(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => 'required|string']);

        PushSubscription::where('user_id', $request->user()->id)
            ->where('endpoint', $request->endpoint)
            ->update(['is_active' => false]);

        return response()->json(['message' => 'Push subscription dinonaktifkan.']);
    }

    /**
     * Replay offline sync queue items submitted by the service worker.
     *
     * POST /api/sync
     */
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'clientId'   => 'required|string|uuid',
            'entityType' => 'required|string|in:consumption,schedule_log,waste_report',
            'action'     => 'required|string|in:create,update,delete',
            'payload'    => 'required|array',
            'performedAt'=> 'required|string',
        ]);

        // Idempotency check
        if (\App\Models\OfflineSyncQueue::alreadyProcessed($validated['clientId'])) {
            return response()->json(['message' => 'Sudah disinkronisasi sebelumnya.', 'idempotent' => true]);
        }

        // Create queue entry and dispatch
        $entry = \App\Models\OfflineSyncQueue::create([
            'user_id'      => $request->user()->id,
            'client_id'    => $validated['clientId'],
            'entity_type'  => $validated['entityType'],
            'action'       => $validated['action'],
            'payload'      => $validated['payload'],
            'status'       => 'pending',
            'performed_at' => $validated['performedAt'],
        ]);

        // Dispatch the appropriate job
        \App\Jobs\ProcessOfflineSyncItem::dispatch($entry);

        return response()->json([
            'message'   => 'Item diterima dan sedang diproses.',
            'server_id' => $entry->id,
        ], 202);
    }

    /**
     * Get the user's pending offline queue count (for UI badge).
     *
     * GET /api/pwa/queue-status
     */
    public function queueStatus(Request $request): JsonResponse
    {
        $pending = \App\Models\OfflineSyncQueue::where('user_id', $request->user()->id)
            ->pending()
            ->count();

        $failed = \App\Models\OfflineSyncQueue::where('user_id', $request->user()->id)
            ->failed()
            ->count();

        return response()->json(compact('pending', 'failed'));
    }

    private function iconList(): array
    {
        $sizes = [72, 96, 128, 144, 152, 192, 384, 512];
        return array_map(fn($size) => [
            'src'     => "/icons/icon-{$size}x{$size}.png",
            'sizes'   => "{$size}x{$size}",
            'type'    => 'image/png',
            'purpose' => 'maskable any',
        ], $sizes);
    }
}
