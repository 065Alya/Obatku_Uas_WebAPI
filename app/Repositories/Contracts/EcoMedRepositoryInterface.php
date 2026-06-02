<?php

namespace App\Repositories\Contracts;

use App\Models\WasteReport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface EcoMedRepositoryInterface extends BaseRepositoryInterface
{
    // ── Waste Reports ──────────────────────────────────────────────────────

    public function getUserWasteReports(int $userId, int $perPage = 10): LengthAwarePaginator;

    public function getAllWasteReports(int $perPage = 15): LengthAwarePaginator;

    public function createWasteReport(int $userId, array $data): WasteReport;

    public function updateReportStatus(int $reportId, string $status): WasteReport;

    public function getWasteStatsByUser(int $userId): array;

    // ── Notification Logs ─────────────────────────────────────────────────

    public function getExpiryNotificationHistory(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function getNotificationStats(int $userId): array;

    // ── Disposal Guides ───────────────────────────────────────────────────

    public function getAllActiveGuides(): Collection;

    public function getGuideByForm(string $form): ?\App\Models\DisposalGuide;
}
