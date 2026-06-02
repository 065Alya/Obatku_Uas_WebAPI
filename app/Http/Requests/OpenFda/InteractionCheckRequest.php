<?php

namespace App\Http\Requests\OpenFda;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a drug interaction check request.
 */
class InteractionCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'drugs'   => ['required', 'array', 'min:1', 'max:5'],
            'drugs.*' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'drugs.required'   => 'At least one drug name is required.',
            'drugs.min'        => 'Provide at least one drug name.',
            'drugs.max'        => 'Maximum 5 drugs can be checked at once.',
            'drugs.*.required' => 'Each drug name must be a non-empty string.',
            'drugs.*.min'      => 'Each drug name must be at least 2 characters.',
        ];
    }

    /** Return the sanitised drug name array. */
    public function drugNames(): array
    {
        return array_map('trim', $this->input('drugs', []));
    }
}
