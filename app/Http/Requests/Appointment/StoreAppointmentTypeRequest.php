<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointments.manage-all') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('appointment_types', 'code')],
            'name' => ['required', 'string', 'max:255', Rule::unique('appointment_types', 'name')],
            'description' => ['nullable', 'string', 'max:1000'],
            'default_duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'requires_approval' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper((string) $this->input('code'))]);
    }
}
