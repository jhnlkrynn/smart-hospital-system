<?php

namespace App\Http\Requests\Consultation;

use App\Enums\DiagnosisStatus;
use App\Enums\DiagnosisType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsultationDiagnosisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('diagnoses.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'diagnosis_catalog_id' => ['nullable', 'exists:diagnosis_catalog,id'],
            'diagnosis_name' => ['nullable', 'string', 'max:255', 'required_without:diagnosis_catalog_id'],
            'diagnosis_code' => ['nullable', 'string', 'max:50'],
            'diagnosis_type' => ['required', Rule::enum(DiagnosisType::class)],
            'diagnosis_status' => ['nullable', Rule::enum(DiagnosisStatus::class)],
            'clinical_notes' => ['nullable', 'string', 'max:5000'],
            'onset_date' => ['nullable', 'date'],
            'resolved_date' => ['nullable', 'date', 'after_or_equal:onset_date'],
            'is_patient_visible' => ['nullable', 'boolean'],
            'sync_to_problem_list' => ['nullable', 'boolean'],
        ];
    }
}
