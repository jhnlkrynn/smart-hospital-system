<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('clinical-attachments.upload') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'is_confidential' => ['nullable', 'boolean'],
            'is_patient_visible' => ['nullable', 'boolean'],
        ];
    }
}
