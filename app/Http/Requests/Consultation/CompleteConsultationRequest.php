<?php

namespace App\Http\Requests\Consultation;

class CompleteConsultationRequest extends UpdateConsultationRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('consultations.complete') || $this->user()?->can('consultations.finalize') || false;
    }

    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'clinical_impression' => ['required', 'string', 'max:10000'],
            'treatment_plan' => ['required', 'string', 'max:10000'],
            'follow_up_instructions' => ['required', 'string', 'max:10000'],
        ]);
    }
}
