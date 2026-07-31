<?php

namespace App\Http\Requests\Employee;

use App\Enums\DepartmentStatus;
use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\Sex;
use App\Enums\UserStatus;
use App\Models\Department;
use App\Support\AccessControl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('employee'));
    }

    public function rules(): array
    {
        $employee = $this->route('employee');

        return [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee->user_id), Rule::unique('employees', 'email')->ignore($employee)],
            'role' => ['required', Rule::in($this->assignableRoles())],
            'account_status' => ['required', Rule::enum(UserStatus::class)],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before:today', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'sex' => ['required', Rule::enum(Sex::class)],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'contact_number' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'city_municipality' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'department_id' => ['required', Rule::exists(Department::class, 'id')->where('status', DepartmentStatus::Active->value)->whereNull('deleted_at')],
            'position' => ['required', 'string', 'max:255'],
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'employment_status' => ['required', Rule::enum(EmploymentStatus::class)],
            'hire_date' => ['required', 'date'],
            'license_number' => ['nullable', 'string', 'max:255'],
            'license_expiration_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'specialization' => [Rule::requiredIf($this->input('role') === 'doctor'), 'nullable', 'string', 'max:255'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'maximum_appointments_per_day' => ['nullable', 'integer', 'min:1', 'max:100'],
            'clinic_room' => ['nullable', 'string', 'max:255'],
            'work_schedule_notes' => ['nullable', 'string', 'max:2000'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_contact_number' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function assignableRoles(): array
    {
        $roles = ['hospital-admin', 'doctor', 'nurse', 'pharmacist', 'laboratory-staff', 'cashier'];

        if ($this->user()->hasRole('super-admin')) {
            array_unshift($roles, 'super-admin');
        }

        return array_values(array_intersect($roles, array_keys(AccessControl::ROLES)));
    }
}
