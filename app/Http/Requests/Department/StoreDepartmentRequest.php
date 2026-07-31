<?php

namespace App\Http\Requests\Department;

use App\Enums\DepartmentStatus;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('departments.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:departments,code'],
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'department_head_employee_id' => ['nullable', Rule::exists(Employee::class, 'id')->whereNull('deleted_at')],
            'status' => ['required', Rule::enum(DepartmentStatus::class)],
        ];
    }
}
