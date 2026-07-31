<?php

namespace App\Http\Requests\Queue;

use App\Enums\TriageAcuity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTriageRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('triage.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'chief_complaint' => ['required', 'string', 'max:255'],
            'pain_scale' => ['required', 'integer', 'min:0', 'max:10'],
            'pregnancy_flag' => ['boolean'],
            'fall_risk_score' => ['required', 'integer', 'min:0', 'max:10'],
            'fall_risk_notes' => ['nullable', 'string', 'max:1000'],
            'acuity' => ['required', Rule::in(array_column(TriageAcuity::cases(), 'value'))],
            'allergies_reviewed' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'blood_pressure_systolic' => ['nullable', 'integer', 'min:40', 'max:260'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'min:30', 'max:180'],
            'pulse_rate' => ['nullable', 'integer', 'min:20', 'max:250'],
            'respiratory_rate' => ['nullable', 'integer', 'min:5', 'max:80'],
            'temperature_c' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'oxygen_saturation' => ['nullable', 'integer', 'min:50', 'max:100'],
            'height_cm' => ['nullable', 'numeric', 'min:30', 'max:260'],
            'weight_kg' => ['nullable', 'numeric', 'min:1', 'max:400'],
        ];
    }

    public function triageData(): array
    {
        return collect($this->validated())->only([
            'chief_complaint', 'pain_scale', 'pregnancy_flag', 'fall_risk_score',
            'fall_risk_notes', 'acuity', 'allergies_reviewed', 'notes',
        ])->all();
    }

    public function vitalData(): array
    {
        return collect($this->validated())->only([
            'blood_pressure_systolic', 'blood_pressure_diastolic', 'pulse_rate',
            'respiratory_rate', 'temperature_c', 'oxygen_saturation', 'height_cm', 'weight_kg',
        ])->filter(fn ($value) => $value !== null)->all();
    }
}
