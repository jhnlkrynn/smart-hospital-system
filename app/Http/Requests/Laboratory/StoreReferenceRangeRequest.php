<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferenceRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('laboratory-tests.manage-reference-ranges') ?? false;
    }

    public function rules(): array
    {
        return [
            'sex' => ['nullable', 'string', 'max:30'],
            'minimum_age_days' => ['nullable', 'integer', 'min:0'],
            'maximum_age_days' => ['nullable', 'integer', 'min:0', 'gte:minimum_age_days'],
            'lower_bound' => ['nullable', 'numeric'],
            'upper_bound' => ['nullable', 'numeric', 'gte:lower_bound'],
            'critical_lower_bound' => ['nullable', 'numeric'],
            'critical_upper_bound' => ['nullable', 'numeric'],
            'text_reference' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
