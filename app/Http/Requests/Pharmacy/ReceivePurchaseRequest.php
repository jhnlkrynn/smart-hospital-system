<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class ReceivePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pharmacy-purchases.receive') === true;
    }

    public function rules(): array
    {
        return [
            'pharmacy_location_id' => ['required', 'exists:pharmacy_locations,id'],
            'received_items' => ['required', 'array', 'min:1'],
            'received_items.*.pharmacy_purchase_item_id' => ['required', 'exists:pharmacy_purchase_items,id'],
            'received_items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'received_items.*.lot_number' => ['required', 'string', 'max:255'],
            'received_items.*.expiration_date' => ['nullable', 'date'],
            'received_items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
