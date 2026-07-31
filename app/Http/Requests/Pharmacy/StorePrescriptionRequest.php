<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('prescriptions.create') === true;
    }

    public function rules(): array
    {
        return [
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'clinical_notes' => ['nullable', 'string', 'max:5000'],
            'patient_instructions' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'finalize' => ['sometimes', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medication_id' => ['required', 'exists:medications,id'],
            'items.*.dose_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.dose_unit_id' => ['nullable', 'exists:medication_units,id'],
            'items.*.route_id' => ['nullable', 'exists:medication_routes,id'],
            'items.*.frequency_id' => ['nullable', 'exists:medication_frequencies,id'],
            'items.*.duration_value' => ['nullable', 'integer', 'min:1', 'max:365'],
            'items.*.duration_unit' => ['nullable', 'in:days,weeks,months'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.quantity_unit_id' => ['nullable', 'exists:medication_units,id'],
            'items.*.instructions' => ['nullable', 'string', 'max:2000'],
            'items.*.pharmacy_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
