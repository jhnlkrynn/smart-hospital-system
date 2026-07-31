<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RescheduleAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointments.reschedule') ?? false;
    }

    public function rules(): array
    {
        return [
            'doctor_employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')],
            'appointment_type_id' => ['nullable', 'integer', Rule::exists('appointment_types', 'id')->where('is_active', true)],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'patient_notes' => ['nullable', 'string', 'max:1000'],
            'staff_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
