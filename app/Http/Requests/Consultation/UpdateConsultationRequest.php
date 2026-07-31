<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('consultations.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'subjective_notes' => ['nullable', 'string', 'max:10000'],
            'objective_notes' => ['nullable', 'string', 'max:10000'],
            'assessment' => ['nullable', 'string', 'max:10000'],
            'clinical_impression' => ['nullable', 'string', 'max:10000'],
            'treatment_plan' => ['nullable', 'string', 'max:10000'],
            'follow_up_instructions' => ['nullable', 'string', 'max:10000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'internal_doctor_notes' => ['nullable', 'string', 'max:10000'],
            'patient_summary' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
