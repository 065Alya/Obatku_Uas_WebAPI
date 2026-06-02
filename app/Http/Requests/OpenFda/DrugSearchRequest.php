<?php

namespace App\Http\Requests\OpenFda;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a medicine search query before it hits the service layer.
 */
class DrugSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled at route level (Sanctum middleware)
    }

    public function rules(): array
    {
        return [
            'q'     => ['required', 'string', 'min:2', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'type'  => ['sometimes', 'string', 'in:any,brand,generic'],
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => 'Search term is required.',
            'q.min'      => 'Search term must be at least 2 characters.',
            'q.max'      => 'Search term must not exceed 100 characters.',
            'limit.max'  => 'You can request a maximum of 50 results.',
            'type.in'    => 'Search type must be one of: any, brand, generic.',
        ];
    }

    /** Parsed limit, defaulting to config value. */
    public function limit(): int
    {
        return (int) $this->input('limit', config('openfda.search.default_limit', 10));
    }

    /** Parsed search type. */
    public function searchType(): string
    {
        return $this->input('type', 'any');
    }
}
