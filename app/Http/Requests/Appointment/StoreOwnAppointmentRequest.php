<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOwnAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('appointments.create') ?? false) && $this->user()?->patient !== null;
    }

    public function rules(): array
    {
        return [
            'doctor_employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],
            'appointment_type_id' => ['required', 'integer', Rule::exists('appointment_types', 'id')->where('is_active', true)],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'reason_for_visit' => ['required', 'string', 'max:255'],
            'patient_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
