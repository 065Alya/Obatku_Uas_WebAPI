<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\ApotekController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EcoMedController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Admin\ArticleManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PWA Static Assets (No Auth Required)
|--------------------------------------------------------------------------
*/

Route::get('/offline',     fn () => response()->file(public_path('offline.html')))->name('pwa.offline');
Route::get('/sw.js',       [PwaController::class, 'serviceWorker'])->name('pwa.sw');
Route::get('/manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest');

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
})->name('home');

// Health Articles (Public)
Route::get('/artikel',       [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{slug}',[ArticleController::class, 'show'])->name('articles.show');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Medicines
    Route::get('/medicines/search', [MedicineController::class, 'search'])->name('medicines.search');
    Route::get('/medicines/{id}/literasi', [MedicineController::class, 'literasi'])->name('medicines.literasi');
    Route::resource('medicines', MedicineController::class);

    // Schedules
    Route::resource('schedules', ScheduleController::class)->except(['show']);
    Route::post('/schedules/{schedule}/log', [ScheduleController::class, 'logIntake'])->name('schedules.log');

    // Consumptions
    Route::get('/consumptions/history', [\App\Http\Controllers\ConsumptionController::class, 'history'])->name('consumptions.history');

    // Family Members
    Route::resource('family', FamilyController::class);

    // Personal Mode (F-02 PRD)
    Route::prefix('personal')->name('personal.')->group(function () {
        Route::get('/',     [PersonalController::class, 'index'])->name('index');
        Route::get('/edit', [PersonalController::class, 'edit'])->name('edit');
        Route::put('/',     [PersonalController::class, 'update'])->name('update');
    });

    // Apotek (F-07 PRD — Page #14)
    Route::get('/apotek', [ApotekController::class, 'index'])->name('apotek.index');

    // ── EcoMed SDG 12 ─────────────────────────────────────────────────────
    Route::prefix('ecomed')->name('ecomed.')->group(function () {
        Route::get('/',                [EcoMedController::class, 'index'])->name('index');
        Route::get('/expiry-alerts',   [EcoMedController::class, 'expiryAlerts'])->name('expiry-alerts');
        Route::get('/disposal-guide',  [EcoMedController::class, 'disposalGuide'])->name('disposal-guide');
        Route::get('/waste-reports',   [EcoMedController::class, 'wasteReports'])->name('waste-reports');
        Route::post('/waste-reports',  [EcoMedController::class, 'storeWasteReport'])->name('waste-reports.store');
        Route::get('/report/export',   [EcoMedController::class, 'exportCsv'])->name('report.export');
        Route::get('/notifications',   [EcoMedController::class, 'notificationHistory'])->name('notifications');
        Route::post('/check-expiry',   [EcoMedController::class, 'checkExpiry'])->name('check-expiry');
    });

    // ── Alerts / Notifications ─────────────────────────────────────────────
    Route::prefix('alerts')->name('alerts.')->group(function () {
        Route::get('/',                    [AlertController::class, 'index'])->name('index');
        Route::post('/mark-all-read',      [AlertController::class, 'markAllRead'])->name('mark-all-read');
        Route::post('/{alert}/read',       [AlertController::class, 'markRead'])->name('read');
        Route::delete('/{alert}',          [AlertController::class, 'destroy'])->name('destroy');
        Route::get('/unread-count',        [AlertController::class, 'unreadCount'])->name('unread-count');
    });

    // ── User Profile ───────────────────────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',                       [ProfileController::class, 'index'])->name('index');
        Route::put('/',                       [ProfileController::class, 'update'])->name('update');
        Route::put('/health',                 [ProfileController::class, 'updatePersonalProfile'])->name('health');
        Route::put('/password',               [ProfileController::class, 'updatePassword'])->name('password');
        Route::get('/settings',               [ProfileController::class, 'settings'])->name('settings');
        Route::put('/settings',               [ProfileController::class, 'updateSettings'])->name('settings.update');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'adminIndex'])->name('dashboard');

    // User Management
    Route::get('/users',                             [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/{id}',                        [UserManagementController::class, 'show'])->name('users.show');
    Route::patch('/users/{id}/toggle-status',        [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');

    // Article Management
    Route::resource('articles', ArticleManagementController::class);

    // Admin CSV Exports
    Route::get('/ecomed/export-csv', [EcoMedController::class, 'adminExportCsv'])->name('ecomed.export-csv');
});



