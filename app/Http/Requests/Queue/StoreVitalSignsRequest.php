<?php

namespace App\Http\Requests\Queue;

use Illuminate\Foundation\Http\FormRequest;

class StoreVitalSignsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('vital-signs.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'blood_pressure_systolic' => ['nullable', 'integer', 'min:40', 'max:260'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'min:30', 'max:180'],
            'pulse_rate' => ['nullable', 'integer', 'min:20', 'max:250'],
            'respiratory_rate' => ['nullable', 'integer', 'min:5', 'max:80'],
            'temperature_c' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'oxygen_saturation' => ['nullable', 'integer', 'min:50', 'max:100'],
            'height_cm' => ['nullable', 'numeric', 'min:30', 'max:260'],
            'weight_kg' => ['nullable', 'numeric', 'min:1', 'max:400'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
