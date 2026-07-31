<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('medications.create') === true;
    }

    public function rules(): array
    {
        return [
            'generic_name' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'medication_category_id' => ['required', 'exists:medication_categories,id'],
            'dosage_form_id' => ['nullable', 'exists:dosage_forms,id'],
            'strength_value' => ['nullable', 'numeric', 'min:0'],
            'strength_unit_id' => ['nullable', 'exists:medication_units,id'],
            'manufacturer_id' => ['nullable', 'exists:medication_manufacturers,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'discontinued', 'restricted', 'out_of_formulary'])],
            'formulary_status' => ['required', 'string', 'max:40'],
            'requires_prescription' => ['sometimes', 'boolean'],
            'is_controlled' => ['sometimes', 'boolean'],
            'is_high_alert' => ['sometimes', 'boolean'],
            'requires_cold_storage' => ['sometimes', 'boolean'],
            'default_route_id' => ['nullable', 'exists:medication_routes,id'],
            'default_frequency_id' => ['nullable', 'exists:medication_frequencies,id'],
            'default_reorder_level' => ['nullable', 'numeric', 'min:0'],
            'default_minimum_stock' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
