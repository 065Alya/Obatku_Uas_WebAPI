<?php

namespace App\Http\Requests\OpenFda;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a literacy card lookup request.
 */
class LiteracyCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'min:2', 'max:150'],
            'co_drugs'  => ['sometimes', 'array', 'max:4'],
            'co_drugs.*'=> ['string', 'min:2', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Drug name is required.',
            'name.min'       => 'Drug name must be at least 2 characters.',
            'co_drugs.max'   => 'You can specify a maximum of 4 concurrent drugs.',
        ];
    }

    /** Co-drug names for interaction enrichment. */
    public function coDrugs(): array
    {
        return array_map('trim', $this->input('co_drugs', []));
    }
}
