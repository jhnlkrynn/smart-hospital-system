<?php

namespace App\Http\Requests\DoctorSchedule;

use App\Enums\ScheduleExceptionType;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('doctor-schedules.manage-exceptions') || $this->user()?->can('doctor-schedules.manage-leave');
    }

    public function rules(): array
    {
        return [
            'doctor_employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],
            'exception_date' => ['required', 'date'],
            'exception_type' => ['required', Rule::in(array_column(ScheduleExceptionType::cases(), 'value'))],
            'start_time' => ['nullable', 'date_format:H:i', 'before:end_time'],
            'end_time' => ['nullable', 'required_with:start_time', 'date_format:H:i'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'is_available' => ['boolean'],
            'maximum_appointments' => ['nullable', 'integer', 'min:1', 'max:200'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $doctor = Employee::with('user.roles')->find($this->input('doctor_employee_id'));
            if (! $doctor?->user?->hasRole('doctor')) {
                $validator->errors()->add('doctor_employee_id', 'The selected employee must have the doctor role.');
            }

            if ($this->input('exception_type') === ScheduleExceptionType::CustomHours->value && (! $this->input('start_time') || ! $this->input('end_time'))) {
                $validator->errors()->add('start_time', 'Custom hours require a start and end time.');
            }

            if ($this->user()?->can('doctor-schedules.manage-own') && ! $this->user()?->can('doctor-schedules.manage-all')) {
                if ((int) $doctor?->id !== (int) $this->user()?->employee?->id) {
                    $validator->errors()->add('doctor_employee_id', 'Doctors can create exceptions only for themselves.');
                }
            }
        });
    }
}
