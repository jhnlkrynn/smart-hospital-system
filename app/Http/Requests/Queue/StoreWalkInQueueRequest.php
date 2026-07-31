<?php

namespace App\Http\Requests\Queue;

use App\Enums\PatientStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWalkInQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('queues.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', Rule::exists('patients', 'id')->where('status', PatientStatus::Active->value)],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'doctor_employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')],
            'is_emergency' => ['boolean'],
            'is_senior_citizen' => ['boolean'],
            'is_pwd' => ['boolean'],
            'is_pregnant' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
