# ObatKu v2.0.0 — Progress Report
**Generated:** 2026-05-31 | **Against:** ObatKu_PRD_v2.0.0.md

---

## Executive Summary

| Category | Count / Score |
|---|---|
| PRD Feature Groups | 9 (F-01 → F-09 + PWA + REST API) |
| **Estimated Overall Completion** | **~79%** |
| Completed Features | 7 of 9 core feature groups |
| Critical Gaps | 4 (Literasi route+view, Apotek/Maps page, `/personal` mode, CSV/PDF export) |
| Technical Debt Items | 6 |

---

## ✅ Completed Features

### F-01 — Authentication & Authorization (2 Roles)
- `AuthController` (web + API) — login, register, logout ✅
- `RoleMiddleware` (`role:admin`) + `EnsureUserIsActive` middleware ✅
- Dual-role login page (Admin / User) on `/login` ✅
- Session via **Laravel Sanctum** (web session + API Bearer token) ✅
- Guest middleware protecting auth routes ✅

### F-03 — Family Member Management
- `FamilyController` — full CRUD (index, create, store, edit, update, destroy) ✅
- `FamilyService` + `FamilyMemberRepository` + `FamilyMemberRepositoryInterface` ✅
- `families`, `family_members` tables + migrations ✅
- Blade views: `family/index`, `family/create`, `family/edit` ✅
- Polymorphic owner relation on `medicines` for family members ✅

### F-04 — Medicine Management & Schedules
- `MedicineController` — full CRUD with ownership authorization ✅
- `MedicineService` + `MedicineRepository` + interface ✅
- `ScheduleController` — full CRUD + `logIntake` action ✅
- `ScheduleService` + `ScheduleRepository` + interface ✅
- `expiry_date` field on `medicines` (required per PRD) ✅
- Blade views: `medicines/{index,create,edit,show}` ✅
- Blade views: `schedules/{index,create,edit}` ✅
- `MedicineCategory` model + seeder ✅

### F-07 — Stock Alerts
- `Alert` model with `ofType()`, `unread()` scopes ✅
- `AlertController` (web + API) — index, mark-read, mark-all-read, destroy, unread-count ✅
- `SendStockAlerts` Artisan command ✅
- Scheduler entry (daily at 09:00 WIB) in `console.php` ✅
- `alerts/index` Blade view ✅
- Stock alert threshold field on medicines ✅

### F-08 — Compliance Dashboard & Reporting (partial)
- `DashboardController` — user dashboard with today's schedules, upcoming, adherence rate, family members ✅
- `DashboardController::adminIndex()` — admin dashboard with platform stats ✅
- `admin/dashboard` Blade view ✅
- `dashboard/index` Blade view (user) ✅
- `ScheduleService::getAdherenceRate()` ✅

### F-09 — EcoMed Module (SDG 12) — **Largely Complete**
- **F-09a Expiry Tracking:** `EcoMedRepository`, `EcoMedService::getExpiryCategorised()`, color-coded expiry status ✅
- **F-09b Expiry Notifications:** `ProcessExpiryNotifications` command (H-90/H-30/H-7/H+0), `expiry_notification_logs` table, scheduler daily at 08:00 WIB ✅
- **F-09c Disposal Guide:** `DisposalGuide` model, `ecomed/disposal-guide` Blade view, seeded guides ✅
- **F-09d Waste Reports:** `WasteReport` model, `ecomed/waste-reports` Blade view + store action, waste stats widget ✅
- **F-09e Integration:** expiry-alert Blade view, notification history, EcoMed index dashboard ✅
- EcoMed API endpoints (`/api/ecomed/*`) — stats, expiring, expired, disposal-guides, waste-reports, check-expiry, notification-history ✅
- `EcoMedApiController` (non-versioned) + `V1/EcoMedController` ✅

### PWA — **Largely Complete**
- `manifest.json` with correct brand colors and icons ✅
- `sw.js` — precache, runtime caching, background sync, push notification, offline fallback ✅
- `offline.html` fallback page ✅
- PWA icon set: 72, 96, 128, 144, 152, 192, 384, 512px ✅
- `PwaController` — serviceWorker, manifest, pushSubscribe, pushUnsubscribe, sync, queueStatus ✅
- `push_subscriptions` + `offline_sync_queue` tables + models ✅
- `VapidService` (manual VAPID signing without external package) ✅
- `PushNotificationJob`, `SendPushNotification`, `ProcessOfflineSyncItem` jobs ✅
- `PushNotificationService` + `WebPushChannel` + `TwilioSmsChannel` ✅
- `GenerateVapidKeys` Artisan command ✅
- `SendMedicineReminders` command (every 15 min, 06:00–23:00 WIB) ✅
- `SendInteractionAlerts` command (weekly, Sundays at 10:00 WIB) ✅
- PWA API routes — `/api/pwa/push-subscribe`, `/api/pwa/push-unsubscribe`, `/api/sync` ✅
- `/offline` and `/sw.js` public routes ✅

### REST API v1 — **Core Endpoints Implemented**
- Auth: `POST /api/v1/login`, `POST /api/v1/register`, `POST /api/v1/logout`, `GET /api/v1/user` ✅
- Medicines: full `apiResource` (index, store, show, update, destroy) ✅
- Schedules: full `apiResource` ✅
- Consumptions: full `apiResource` ✅
- Alerts: index, mark-read, mark-all-read (V1) ✅
- EcoMed V1: stats, expiring, waste-reports store ✅
- OpenFDA V1: search, interactions ✅
- `VerifyApiKey` middleware on `/api/v1/*` ✅
- API Resource classes: `MedicineResource`, `ScheduleResource`, `ConsumptionResource`, `AlertResource` ✅
- Non-versioned API: OpenFDA proxy, EcoMed, Alerts, PWA subscriptions ✅

### OpenFDA Integration
- `DrugSearchService`, `DrugInteractionService`, `DrugLiteracyService`, `OpenFdaCacheService` ✅
- `OpenFdaController` (non-versioned) — search, genericName, checkInteractions, quickInteraction, literacyCard, cacheStatus, flushCache ✅
- `V1/OpenFdaController` — search, checkInteractions ✅

### Repository Pattern
- `BaseRepository` + `BaseRepositoryInterface` ✅
- `MedicineRepository` + interface ✅
- `FamilyMemberRepository` + interface ✅
- `ScheduleRepository` + interface ✅
- `EcoMedRepository` + interface ✅
- **5 concrete repository classes** (PRD targets 6) — `UserRepository` is missing ✅

### Supporting Infrastructure
- `ActivityLogService` + `ActivityLog` model ✅
- `HealthArticle` model + `ArticleController` + `ArticleService` ✅
- `ArticleManagementController` (Admin CRUD for articles) ✅
- `UserManagementController` (Admin user list + toggle-status) ✅
- Article Blade views: `articles/index`, `articles/show` ✅
- Admin article management views (in `admin/articles/`) ✅
- Database: 24 migrations (exceeds the 11 in PRD — extra tables for extended functionality) ✅
- `Notifications` directory with push notification classes ✅

---

## 🔄 Features In Progress / Partially Implemented

### F-02 — Personal Profile Mode
- `ProfileController` with `index`, `update`, `updatePersonalProfile`, `updatePassword` ✅
- `PersonalProfile` model + migration + extended migration ✅
- `profile/index` Blade view ✅
- **⚠️ Missing:** The PRD-specified `/personal` route and dedicated Personal Mode dashboard (F-02 specifies `/personal` and `/personal/edit` as separate pages from `/profile`). Currently collapsed into `/profile/*`.

### F-06 — Drug Literacy Cards
- `DrugLiteracyService` — fetches from OpenFDA, builds 4-category literacy card ✅
- `OpenFdaController::literacyCard()` API endpoint ✅
- **⚠️ Missing:** `/medicines/{id}/literasi` web **route** is not defined in `web.php`
- **⚠️ Missing:** `medicines/literasi.blade.php` view does not exist
- The backend service is ready but the frontend page (PRD page #11) is absent.

### F-07 — Apotek Terdekat (Google Maps)
- Stock alert triggers and alert system work ✅
- **⚠️ Missing:** `/apotek` route and associated controller/view — no Google Maps Places integration in web routes
- **⚠️ Missing:** `ApotekController` does not exist

### F-08 — Export CSV/PDF
- Dashboard adherence stats are computed ✅
- **⚠️ Missing:** No export route, no `dompdf`/`Laravel-Excel` dependency, no CSV/PDF generation logic
- PRD specifies export at `/ecomed/report` (CSV) and admin export

### F-05 — Drug Interaction Detection
- `DrugInteractionService` + `SendInteractionAlerts` Artisan command ✅
- API endpoint for interaction check ✅
- **⚠️ Partial:** Automatic interaction check **on medicine save** (observer/event) is not evident — currently limited to API and scheduled weekly scan

### Family Detail View (`/families/members/{id}`)
- `FamilyController` has index/create/edit/update/destroy but **no `show()` method** ✅
- **⚠️ Missing:** `family/show.blade.php` view (PRD page #8: detail anggota with medicines, schedules, history, literasi)

---

## ❌ Missing Features

| # | PRD Requirement | Gap |
|---|---|---|
| F-02 | `/personal` & `/personal/edit` dedicated pages | No route, no view — merged into `/profile` |
| F-06 | `/medicines/{id}/literasi` Literacy Card page | Route, view, and web controller action all absent |
| F-07 | `/apotek` — Google Maps Places page | No route, controller, or view |
| F-08 | CSV/PDF export (admin + user) | No export implementation |
| Page #8 | `/families/members/{id}` detail page | `show()` method and view missing from FamilyController |
| REST API | `GET /api/personal`, `PUT /api/personal` (endpoints 5 & 6 in PRD table) | No `/api/v1/personal` route group |
| REST API | `GET /api/families`, `POST /api/families/members`, `PUT /api/families/members/{id}` (endpoints 7–9) | No `/api/v1/families` route group |
| REST API | `GET /api/consumptions/history` (endpoint 16) — separate from generic index | Not implemented as a distinct endpoint |
| REST API | `GET /api/stock/alerts` (endpoint 17 — JWT + API Key) | Not in V1 routes |
| REST API | `GET /api/ecomed/disposal-guide/{type}` (endpoint 21) | V1 EcoMed missing disposal-guide endpoint |
| REST API | `GET /api/ecomed/report` (endpoint 22) | V1 EcoMed missing report endpoint |
| PWA | Twilio WhatsApp for EcoMed (H-30, H-7 WhatsApp messages) | `TwilioSmsChannel` exists but integration with EcoMed expiry scheduler is unclear |
| Admin | EcoMed global statistics on admin dashboard | Admin dashboard doesn't fetch EcoMed stats |
| `/profile/settings` | Notification settings toggle, accessibility font toggle | Profile page has no notification preference UI |

---

## 🛠 Technical Debt

| # | Issue | Severity | Location |
|---|---|---|---|
| TD-01 | **Duplicated ownership authorization logic** — the 3-condition `isOwner` check is copy-pasted across `MedicineController::show`, `edit`, `update`, `destroy` (4 methods) | Medium | `MedicineController.php` |
| TD-02 | **`ActivityLogService::log()` signature inconsistency** — some call sites pass `(userId, message)`, others pass `('action', 'message', 'Model', id)` | High | `EcoMedController`, `ProfileController` vs `MedicineController` |
| TD-03 | **Missing `UserRepository`** — PRD states 6 repo classes; only 5 exist. User-level queries are scattered directly in controllers | Low | `app/Repositories/` |
| TD-04 | **No FormRequest classes** — all validation is inline in controllers. No dedicated `StoreMedicineRequest`, `StoreScheduleRequest`, etc. | Medium | All controllers |
| TD-05 | **`expiry_date` validation not enforced as required** — PRD says `expiry_date` is mandatory (`wajib`); `MedicineController` uses `'expiry_date' => 'nullable|date'` | High | `MedicineController.php` L55 |
| TD-06 | **`FamilyController` missing `show()` method** — Resource route registered via `Route::resource('family', ...)` which expects a `show` method, but only index/create/edit/update/destroy exist; visiting `/family/{id}` will 404 | High | `FamilyController.php` |

---

## Completion Percentage

### By Feature Group

| Feature | PRD Scope | Status | % |
|---|---|---|---|
| F-01 Auth & Roles | Login/register, 2 roles, middleware | ✅ Complete | 100% |
| F-02 Personal Profile | Personal mode, `/personal` page | ⚠️ Partial (profile merged) | 60% |
| F-03 Family Management | Multi-member CRUD, family dashboard | ⚠️ Partial (no show/detail) | 75% |
| F-04 Medicine + Schedule | CRUD, expiry_date, schedules, confirmations | ✅ Mostly complete | 90% |
| F-05 Drug Interaction | OpenFDA check, alerts | ⚠️ Partial (no auto-on-save trigger) | 70% |
| F-06 Literacy Cards | 4-category cards, toggle font | ⚠️ Partial (service ready, no web page) | 40% |
| F-07 Stock + Apotek | Alert system done; Maps page missing | ⚠️ Partial | 55% |
| F-08 Compliance & Export | Stats done; export missing | ⚠️ Partial | 60% |
| F-09 EcoMed Module | Expiry, disposal, waste report, notifs | ✅ Largely complete | 90% |
| PWA | SW, manifest, push, offline sync | ✅ Largely complete | 88% |
| REST API (25 ep) | Versioned + non-versioned | ⚠️ ~18/25 implemented | 72% |
| Repository Pattern | 6 repos + interfaces | ⚠️ 5/6 | 83% |
| Blade Views (18 pages) | All PRD-defined pages | ⚠️ ~13/18 | 72% |

### Overall Estimated Completion: **~79%**

> The core application is functional and demonstrable. The major gaps are the Literacy Card web page (F-06), the Google Maps/Apotek page (F-07), the Family Member detail view, several V1 API endpoints covering personal/family resources, and CSV/PDF export.

---

## Recommended Priority Actions (for UAS readiness)

1. **[HIGH]** Add `/medicines/{id}/literasi` route + `literasi.blade.php` view — this is a PRD-highlighted feature on its own page (#11)
2. **[HIGH]** Add `FamilyController::show()` + `family/show.blade.php` (PRD page #8)
3. **[HIGH]** Fix `ActivityLogService::log()` signature — standardize across all callers
4. **[HIGH]** Make `expiry_date` required in medicine store validation
5. **[MEDIUM]** Add `/apotek` stub page with Google Maps iframe embed (even static is sufficient for demo)
6. **[MEDIUM]** Add V1 API route groups for `/api/v1/personal` and `/api/v1/families`
7. **[MEDIUM]** Add CSV export for EcoMed waste reports (simple Laravel response with headers)
8. **[LOW]** Add `UserRepository` to reach PRD's stated 6 repository classes
9. **[LOW]** Extract `isOwner` check in `MedicineController` into a policy or helper method
