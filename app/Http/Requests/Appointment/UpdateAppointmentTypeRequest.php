<?php

namespace App\Http\Requests\Appointment;

use App\Models\AppointmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointments.manage-all') ?? false;
    }

    public function rules(): array
    {
        /** @var AppointmentType|null $type */
        $type = $this->route('appointment_type');

        return [
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('appointment_types', 'code')->ignore($type?->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('appointment_types', 'name')->ignore($type?->id)],
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
