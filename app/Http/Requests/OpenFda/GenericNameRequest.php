<?php

namespace App\Http\Requests\OpenFda;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a generic name autofill request.
 */
class GenericNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_name' => ['required', 'string', 'min:2', 'max:100'],
            'limit'      => ['sometimes', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'brand_name.required' => 'Brand name is required for generic name lookup.',
            'brand_name.min'      => 'Brand name must be at least 2 characters.',
        ];
    }
}
