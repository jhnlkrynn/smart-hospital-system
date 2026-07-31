<?php

namespace App\Http\Requests\Appointment;

use App\Enums\PatientStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointments.create') || $this->user()?->can('appointments.create-for-patient');
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', Rule::exists('patients', 'id')->where('status', PatientStatus::Active->value)],
            'doctor_employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],
            'appointment_type_id' => ['required', 'integer', Rule::exists('appointment_types', 'id')->where('is_active', true)],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'reason_for_visit' => ['required', 'string', 'max:255'],
            'patient_notes' => ['nullable', 'string', 'max:1000'],
            'staff_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
