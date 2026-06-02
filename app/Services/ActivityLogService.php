<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Record an activity.
     */
    public static function log(
        string $action,
        ?string $description = null,
        ?string $modelType = null,
        ?int $modelId = null,
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Get recent activity for a user.
     */
    public static function getRecent(int $userId, int $limit = 10)
    {
        return ActivityLog::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
