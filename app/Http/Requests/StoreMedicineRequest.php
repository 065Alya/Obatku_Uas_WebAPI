<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'generic_name'          => 'nullable|string|max:255',
            'category_id'           => 'nullable|exists:medicine_categories,id',
            'family_member_id'      => 'nullable|exists:family_members,id',
            'dosage'                => 'nullable|string|max:100',
            'unit'                  => 'required|string|max:50',
            'form'                  => 'required|string|max:50',
            'manufacturer'          => 'nullable|string|max:255',
            'description'           => 'nullable|string|max:1000',
            'side_effects'          => 'nullable|string|max:1000',
            'stock'                 => 'required|integer|min:0',
            'stock_alert_threshold' => 'required|integer|min:0',
            'price'                 => 'nullable|numeric|min:0',
            'expiry_date'           => 'required|date|after_or_equal:today',
        ];
    }
}
