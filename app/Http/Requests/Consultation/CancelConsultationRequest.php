<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class CancelConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('consultations.cancel') ?? false;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:5', 'max:1000']];
    }
}
