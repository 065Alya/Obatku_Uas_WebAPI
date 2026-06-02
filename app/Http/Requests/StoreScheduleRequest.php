<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medicine_id'       => 'required|exists:medicines,id',
            'family_member_id'  => 'nullable|exists:family_members,id',
            'schedule_time'     => 'required|date_format:H:i',
            'frequency'         => 'required|in:daily,twice_daily,three_daily,weekly,monthly,as_needed',
            'dosage_amount'     => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
            'start_date'        => 'required|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
