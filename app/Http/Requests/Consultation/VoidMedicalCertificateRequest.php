<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class VoidMedicalCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('medical-certificates.void') ?? false;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:10', 'max:1000']];
    }
}
