<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pharmacy-inventory.adjust') === true;
    }

    public function rules(): array
    {
        return [
            'adjustment_type' => ['required', 'in:increase,decrease'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
}
