<?php

namespace App\Jobs;

use App\Models\Consumption;
use App\Models\OfflineSyncQueue;
use App\Models\ScheduleLog;
use App\Models\WasteReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProcessOfflineSyncItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly OfflineSyncQueue $entry,
    ) {}

    public function handle(): void
    {
        $this->entry->update(['status' => OfflineSyncQueue::STATUS_PROCESSING]);

        try {
            $result = match ($this->entry->entity_type) {
                'consumption'   => $this->handleConsumption(),
                'schedule_log'  => $this->handleScheduleLog(),
                'waste_report'  => $this->handleWasteReport(),
                default         => throw new \InvalidArgumentException("Unknown entity type: {$this->entry->entity_type}"),
            };

            $this->entry->markSynced(200);

            Log::info('[OfflineSync] Processed item', [
                'client_id'   => $this->entry->client_id,
                'entity_type' => $this->entry->entity_type,
                'action'      => $this->entry->action,
                'result_id'   => $result?->id,
            ]);
        } catch (\Throwable $e) {
            $this->entry->markFailed($e->getMessage());

            Log::error('[OfflineSync] Failed to process item', [
                'client_id' => $this->entry->client_id,
                'error'     => $e->getMessage(),
            ]);

            throw $e; // Let queue retry
        }
    }

    private function handleConsumption(): Consumption
    {
        $payload = $this->validate($this->entry->payload, [
            'medicine_id'          => 'required|exists:medicines,id',
            'medicine_schedule_id' => 'nullable|exists:medicine_schedules,id',
            'quantity'             => 'required|numeric|min:0.01',
            'unit'                 => 'required|string|max:50',
            'status'               => 'required|in:taken,skipped,missed',
            'consumed_at'          => 'required|date',
            'notes'                => 'nullable|string|max:500',
        ]);

        return Consumption::create([
            ...$payload,
            'user_id'    => $this->entry->user_id,
            'offline_id' => $this->entry->client_id,
            'is_synced'  => true,
        ]);
    }

    private function handleScheduleLog(): ScheduleLog
    {
        $payload = $this->validate($this->entry->payload, [
            'medicine_schedule_id' => 'required|exists:medicine_schedules,id',
            'status'               => 'required|in:taken,skipped,missed',
            'taken_at'             => 'nullable|date',
            'skipped_reason'       => 'nullable|string|max:255',
            'notes'                => 'nullable|string|max:500',
        ]);

        return ScheduleLog::create($payload);
    }

    private function handleWasteReport(): WasteReport
    {
        $payload = $this->validate($this->entry->payload, [
            'medicine_name'   => 'required|string|max:255',
            'medicine_form'   => 'required|string|max:100',
            'quantity'        => 'required|numeric|min:0.01',
            'unit'            => 'required|string|max:50',
            'disposal_method' => 'required|in:pharmacy_return,household_trash,collection_point,flush,bury',
            'disposed_at'     => 'required|date',
            'notes'           => 'nullable|string|max:1000',
        ]);

        return WasteReport::create([
            ...$payload,
            'user_id' => $this->entry->user_id,
            'status'  => 'pending',
        ]);
    }

    private function validate(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(
                'Validation failed: ' . $validator->errors()->first()
            );
        }

        return $validator->validated();
    }

    public function failed(\Throwable $exception): void
    {
        $this->entry->update([
            'status'        => OfflineSyncQueue::STATUS_FAILED,
            'error_message' => $exception->getMessage(),
        ]);
    }
}
