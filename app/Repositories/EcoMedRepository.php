<?php

namespace App\Repositories;

use App\Models\DisposalGuide;
use App\Models\ExpiryNotificationLog;
use App\Models\WasteReport;
use App\Repositories\Contracts\EcoMedRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EcoMedRepository extends BaseRepository implements EcoMedRepositoryInterface
{
    public function __construct(WasteReport $model)
    {
        parent::__construct($model);
    }

    /* ─── Waste Reports ─── */

    public function getUserWasteReports(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return WasteReport::where('user_id', $userId)
            ->with('medicine')
            ->orderByDesc('disposed_at')
            ->paginate($perPage);
    }

    public function getAllWasteReports(int $perPage = 15): LengthAwarePaginator
    {
        return WasteReport::with(['user', 'medicine'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function createWasteReport(int $userId, array $data): WasteReport
    {
        return WasteReport::create(array_merge($data, ['user_id' => $userId]));
    }

    public function updateReportStatus(int $reportId, string $status): WasteReport
    {
        $report = WasteReport::findOrFail($reportId);
        $report->update(['status' => $status]);
        return $report->fresh();
    }

    public function getWasteStatsByUser(int $userId): array
    {
        $reports = WasteReport::where('user_id', $userId);

        return [
            'total'          => (clone $reports)->count(),
            'verified'       => (clone $reports)->where('status', 'verified')->count(),
            'pending'        => (clone $reports)->where('status', 'pending')->count(),
            'total_quantity' => (float) (clone $reports)->where('status', 'verified')->sum('quantity'),
            'by_method'      => (clone $reports)
                ->selectRaw('disposal_method, count(*) as total')
                ->groupBy('disposal_method')
                ->pluck('total', 'disposal_method'),
        ];
    }

    /* ─── Notification Logs ─── */

    public function getExpiryNotificationHistory(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return ExpiryNotificationLog::where('user_id', $userId)
            ->with('medicine')
            ->orderByDesc('sent_at')
            ->paginate($perPage);
    }

    public function getNotificationStats(int $userId): array
    {
        $logs = ExpiryNotificationLog::where('user_id', $userId);

        return [
            'total_sent'    => (clone $logs)->count(),
            'by_channel'    => (clone $logs)
                ->selectRaw('channel, count(*) as total')
                ->groupBy('channel')
                ->pluck('total', 'channel'),
            'by_threshold'  => (clone $logs)
                ->selectRaw('days_threshold, count(*) as total')
                ->groupBy('days_threshold')
                ->pluck('total', 'days_threshold'),
            'last_sent_at'  => (clone $logs)->max('sent_at'),
        ];
    }

    /* ─── Disposal Guides ─── */

    public function getAllActiveGuides(): Collection
    {
        return DisposalGuide::where('is_active', true)
            ->orderBy('medicine_form')
            ->get();
    }

    public function getGuideByForm(string $form): ?DisposalGuide
    {
        return DisposalGuide::where('medicine_form', $form)
            ->where('is_active', true)
            ->first();
    }
}
