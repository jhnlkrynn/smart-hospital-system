<?php

namespace App\Http\Requests\DoctorSchedule;

use App\Enums\DayOfWeek;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('doctor-schedules.create') || $this->user()?->can('doctor-schedules.manage-own');
    }

    public function rules(): array
    {
        return [
            'doctor_employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],
            'day_of_week' => ['required', Rule::in(array_column(DayOfWeek::cases(), 'value'))],
            'start_time' => ['required', 'date_format:H:i', 'before:end_time'],
            'end_time' => ['required', 'date_format:H:i'],
            'slot_duration_minutes' => ['required', 'integer', 'min:5', 'max:240'],
            'break_start_time' => ['nullable', 'required_with:break_end_time', 'date_format:H:i', 'after:start_time', 'before:break_end_time'],
            'break_end_time' => ['nullable', 'required_with:break_start_time', 'date_format:H:i', 'before:end_time'],
            'maximum_appointments' => ['required', 'integer', 'min:1', 'max:200'],
            'clinic_room' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $doctor = Employee::with('user.roles')->find($this->input('doctor_employee_id'));
            if (! $doctor?->user?->hasRole('doctor')) {
                $validator->errors()->add('doctor_employee_id', 'The selected employee must have the doctor role.');
            }

            if ($this->user()?->can('doctor-schedules.manage-own') && ! $this->user()?->can('doctor-schedules.manage-all')) {
                if ((int) $doctor?->id !== (int) $this->user()?->employee?->id) {
                    $validator->errors()->add('doctor_employee_id', 'Doctors can manage only their own schedule.');
                }
            }
        });
    }
}
