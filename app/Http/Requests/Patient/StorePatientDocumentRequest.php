<?php

namespace App\Http\Requests\Patient;

use App\Enums\PatientDocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageDocuments', $this->route('patient'));
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', Rule::enum(PatientDocumentType::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
