<?php

namespace App\Http\Requests\Consultation;

class UpdateMedicalCertificateRequest extends StoreMedicalCertificateRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('medical-certificates.update') ?? false;
    }
}
