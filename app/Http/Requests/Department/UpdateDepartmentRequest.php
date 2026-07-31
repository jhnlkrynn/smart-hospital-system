<?php

namespace App\Http\Requests\Department;

use App\Enums\DepartmentStatus;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('department'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
    }

    public function rules(): array
    {
        $department = $this->route('department');

        return [
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('departments', 'code')->ignore($department)],
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department)],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'department_head_employee_id' => ['nullable', Rule::exists(Employee::class, 'id')->where('department_id', $department->id)->whereNull('deleted_at')],
            'status' => ['required', Rule::enum(DepartmentStatus::class)],
        ];
    }
}
