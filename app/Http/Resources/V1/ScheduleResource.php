<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'medicine'      => $this->whenLoaded('medicine', fn() => [
                'id'   => $this->medicine?->id,
                'name' => $this->medicine?->medicine_name,
                'unit' => $this->medicine?->unit,
                'form' => $this->medicine?->form,
            ]),
            'family_member' => $this->whenLoaded('familyMember', fn() => [
                'id'   => $this->familyMember?->id,
                'name' => $this->familyMember?->name,
            ]),
            'schedule_time'  => $this->schedule_time instanceof \Carbon\Carbon
                ? $this->schedule_time->format('H:i')
                : $this->schedule_time,
            'frequency'      => $this->frequency,
            'dosage_amount'  => $this->dosage_amount,
            'notes'          => $this->notes,
            'start_date'     => $this->start_date?->toDateString(),
            'end_date'       => $this->end_date?->toDateString(),
            'is_active'      => $this->is_active,
            'today_logs'     => $this->whenLoaded('logs', fn() =>
                $this->logs->map(fn($log) => [
                    'id'       => $log->id,
                    'status'   => $log->status,
                    'taken_at' => $log->taken_at?->toISOString(),
                    'notes'    => $log->notes,
                ])
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
