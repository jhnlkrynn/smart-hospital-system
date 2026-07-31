<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class AcknowledgeAllergyWarningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('prescriptions.finalize') === true || $this->user()?->can('prescriptions.review') === true;
    }

    public function rules(): array
    {
        return ['override_reason' => ['required', 'string', 'min:5', 'max:2000']];
    }
}
