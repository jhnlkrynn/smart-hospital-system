<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class QuarantineStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pharmacy-inventory.quarantine') === true;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:5', 'max:2000']];
    }
}
