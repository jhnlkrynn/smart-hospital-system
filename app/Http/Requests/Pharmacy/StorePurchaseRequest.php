<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pharmacy-purchases.create') === true;
    }

    public function rules(): array
    {
        return [
            'pharmacy_supplier_id' => ['required', 'exists:pharmacy_suppliers,id'],
            'order_date' => ['nullable', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medication_id' => ['required', 'exists:medications,id'],
            'items.*.ordered_quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_id' => ['nullable', 'exists:medication_units,id'],
        ];
    }
}
