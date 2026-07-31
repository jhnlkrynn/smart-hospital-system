<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('medical-certificates.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'purpose' => ['required', 'string', 'max:255'],
            'clinical_summary' => ['required', 'string', 'max:5000'],
            'recommendation' => ['required', 'string', 'max:5000'],
            'rest_from' => ['nullable', 'date'],
            'rest_until' => ['nullable', 'date', 'after_or_equal:rest_from'],
        ];
    }
}
