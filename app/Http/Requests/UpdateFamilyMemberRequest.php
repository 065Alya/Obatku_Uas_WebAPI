<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFamilyMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'relationship'  => 'required|string|max:50',
            'date_of_birth' => 'nullable|date|before:today',
            'health_notes'  => 'nullable|string',
        ];
    }
}
