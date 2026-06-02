<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\Api\EcoMedApiController;
use App\Http\Controllers\Api\OpenFdaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PwaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

Route::post('/login',    [AuthController::class, 'apiLogin']);
Route::post('/register', [AuthController::class, 'apiRegister']);

/*
|--------------------------------------------------------------------------
| Authenticated API Routes (Sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user',    fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'apiLogout']);

    // ── EcoMed ────────────────────────────────────────────────────────────
    Route::prefix('ecomed')->name('api.ecomed.')->group(function () {
        Route::get('/stats',                          [EcoMedApiController::class, 'stats']);
        Route::get('/expiring',                       [EcoMedApiController::class, 'expiring']);
        Route::get('/expired',                        [EcoMedApiController::class, 'expired']);
        Route::get('/disposal-guides',                [EcoMedApiController::class, 'disposalGuides']);
        Route::get('/disposal-guides/{form}',         [EcoMedApiController::class, 'disposalGuideByForm']);
        Route::get('/waste-reports',                  [EcoMedApiController::class, 'wasteReports']);
        Route::post('/waste-reports',                 [EcoMedApiController::class, 'storeWasteReport']);
        Route::post('/check-expiry',                  [EcoMedApiController::class, 'checkExpiry']);
        Route::get('/notification-history',           [EcoMedApiController::class, 'notificationHistory']);
    });

    // ── OpenFDA Proxy ─────────────────────────────────────────────────────
    // All keys are kept server-side; clients never touch the OpenFDA API directly.
    Route::prefix('openfda')->name('api.openfda.')->group(function () {

        // Medicine search
        Route::get('/search',          [OpenFdaController::class, 'search'])          ->name('search');

        // Generic name autofill
        Route::get('/generic-name',    [OpenFdaController::class, 'genericName'])     ->name('generic-name');

        // Drug interaction detection
        Route::post('/interactions',   [OpenFdaController::class, 'checkInteractions'])->name('interactions');
        Route::get('/interactions/quick', [OpenFdaController::class, 'quickInteraction'])->name('interactions.quick');

        // Medicine literacy cards
        Route::get('/literacy-card',   [OpenFdaController::class, 'literacyCard'])   ->name('literacy-card');

        // Cache management
        Route::get('/cache/status',    [OpenFdaController::class, 'cacheStatus'])    ->name('cache.status');
        Route::delete('/cache',        [OpenFdaController::class, 'flushCache'])     ->name('cache.flush');
    });

    // ── Alerts ────────────────────────────────────────────────────────────
    Route::prefix('alerts')->group(function () {
        Route::get('/unread-count',         [AlertController::class, 'unreadCount']);
        Route::post('/mark-all-read',       [AlertController::class, 'markAllRead']);
        Route::post('/{alert}/read',        [AlertController::class, 'markRead']);
        Route::delete('/{alert}',           [AlertController::class, 'destroy']);
    });

    // ── PWA: Push Subscriptions & Offline Sync ────────────────────────────
    Route::prefix('pwa')->group(function () {
        Route::post('/push-subscribe',   [PwaController::class, 'pushSubscribe']);
        Route::post('/push-unsubscribe', [PwaController::class, 'pushUnsubscribe']);
        Route::get('/queue-status',      [PwaController::class, 'queueStatus']);
    });

    // ── Offline Sync Replay (called by Service Worker) ────────────────────
    Route::post('/sync', [PwaController::class, 'sync']);
});

/*
|--------------------------------------------------------------------------
| V1 API Routes (Versioned REST API)
|--------------------------------------------------------------------------
| Requires API Key (`x-api-key` header) for all routes.
| Sanctum authentication for protected routes.
*/
Route::prefix('v1')
    ->as('api.')
    ->middleware('api.key')
    ->group(function () {
    
    // Auth Routes
    Route::post('/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    Route::post('/register', [\App\Http\Controllers\Api\V1\AuthController::class, 'register']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        Route::get('/user', [\App\Http\Controllers\Api\V1\AuthController::class, 'user']);

        // Resources
        Route::apiResource('medicines', \App\Http\Controllers\Api\V1\MedicineController::class);
        Route::apiResource('schedules', \App\Http\Controllers\Api\V1\ScheduleController::class);

        // Consumptions — history must precede the resource to avoid {id} capturing "history"
        Route::get('/consumptions/history', [\App\Http\Controllers\Api\V1\ConsumptionController::class, 'history']);
        Route::apiResource('consumptions', \App\Http\Controllers\Api\V1\ConsumptionController::class);

        // Personal Profile (F-02 PRD)
        Route::get('/personal',  [\App\Http\Controllers\Api\V1\PersonalController::class, 'show']);
        Route::put('/personal',  [\App\Http\Controllers\Api\V1\PersonalController::class, 'update']);

        // Family Members
        Route::get('/families',                        [\App\Http\Controllers\Api\V1\FamilyController::class, 'index']);
        Route::post('/families/members',               [\App\Http\Controllers\Api\V1\FamilyController::class, 'storeMember']);
        Route::put('/families/members/{id}',           [\App\Http\Controllers\Api\V1\FamilyController::class, 'updateMember']);

        // Alerts
        Route::prefix('alerts')->group(function () {
            Route::get('/',              [\App\Http\Controllers\Api\V1\AlertController::class, 'index']);
            Route::post('/mark-read',    [\App\Http\Controllers\Api\V1\AlertController::class, 'markAllRead']);
            Route::patch('/{id}/read',   [\App\Http\Controllers\Api\V1\AlertController::class, 'markRead']);
            Route::get('/unread-count',  [\App\Http\Controllers\Api\V1\AlertController::class, 'unreadCount']);
        });

        // Stock Alerts (F-07 PRD)
        Route::get('/stock/alerts', [\App\Http\Controllers\Api\V1\AlertController::class, 'stockAlerts']);

        // EcoMed — disposal-guide and report must precede wildcards
        Route::prefix('ecomed')->group(function () {
            Route::get('/stats',                    [\App\Http\Controllers\Api\V1\EcoMedController::class, 'stats']);
            Route::get('/expiring',                 [\App\Http\Controllers\Api\V1\EcoMedController::class, 'expiring']);
            Route::get('/report',                   [\App\Http\Controllers\Api\V1\EcoMedController::class, 'report']);
            Route::get('/disposal-guide/{type}',    [\App\Http\Controllers\Api\V1\EcoMedController::class, 'disposalGuide']);
            Route::post('/waste-reports',           [\App\Http\Controllers\Api\V1\EcoMedController::class, 'storeWasteReport']);
        });

        // OpenFDA Proxy
        Route::prefix('openfda')->group(function () {
            Route::get('/search',        [\App\Http\Controllers\Api\V1\OpenFdaController::class, 'search']);
            Route::post('/interactions', [\App\Http\Controllers\Api\V1\OpenFdaController::class, 'checkInteractions']);
        });
    });
});
