<?php

namespace App\Services\Employee;

use App\Enums\EmploymentStatus;
use App\Enums\UserStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\ReferenceNumberService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    public function __construct(
        private readonly ReferenceNumberService $references,
        private readonly AuditLogService $auditLog,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data, User $actor): Employee
    {
        $photoPath = null;

        if (($data['profile_photo'] ?? null) instanceof UploadedFile) {
            $photoPath = $data['profile_photo']->store('employee-photos', 'public');
        }

        try {
            return DB::transaction(function () use ($data, $actor, $photoPath): Employee {
                $user = User::create([
                    'name' => trim($data['first_name'].' '.$data['last_name']),
                    'email' => $data['email'],
                    'password' => Hash::make($data['temporary_password']),
                    'email_verified_at' => now(),
                    'status' => $data['account_status'] ?? UserStatus::Active->value,
                ]);

                $user->assignRole($data['role']);

                $employee = new Employee($this->employeeData($data));
                $employee->employee_number = $this->references->employeeNumber();
                $employee->user_id = $user->id;
                $employee->profile_photo_path = $photoPath;
                $employee->created_by = $actor->id;
                $employee->updated_by = $actor->id;
                $employee->save();

                $this->auditLog->record($actor, 'created', 'employees', $employee, 'Employee and linked account created.', null, [
                    'employee_id' => $employee->id,
                    'user_id' => $user->id,
                    'role' => $data['role'],
                ], request());

                return $employee;
            });
        } catch (\Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Employee $employee, array $data, User $actor): Employee
    {
        $oldPhoto = $employee->profile_photo_path;
        $newPhoto = null;

        if (($data['profile_photo'] ?? null) instanceof UploadedFile) {
            $newPhoto = $data['profile_photo']->store('employee-photos', 'public');
        }

        try {
            return DB::transaction(function () use ($employee, $data, $actor, $newPhoto, $oldPhoto): Employee {
                $old = $employee->fresh(['user'])->toArray();
                $roleChanged = isset($data['role']) && ! $employee->user->hasRole($data['role']);

                $employee->fill($this->employeeData($data));
                if ($newPhoto) {
                    $employee->profile_photo_path = $newPhoto;
                }
                $employee->updated_by = $actor->id;
                $employee->save();

                $employee->user->forceFill([
                    'name' => $employee->full_name,
                    'email' => $employee->email,
                    'status' => $data['account_status'] ?? $employee->user->status,
                ])->save();

                if (isset($data['role'])) {
                    $employee->user->syncRoles([$data['role']]);
                }

                if ($newPhoto && $oldPhoto) {
                    Storage::disk('public')->delete($oldPhoto);
                }

                $this->auditLog->record($actor, $roleChanged ? 'role_changed' : 'updated', 'employees', $employee, 'Employee updated.', $old, [
                    'employee' => $employee->fresh()->toArray(),
                    'role' => $data['role'] ?? null,
                ], request());

                return $employee;
            });
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }
    }

    public function archive(Employee $employee, User $actor): void
    {
        DB::transaction(function () use ($employee, $actor): void {
            Department::query()
                ->where('department_head_employee_id', $employee->id)
                ->update(['department_head_employee_id' => null, 'updated_by' => $actor->id]);

            $employee->update([
                'employment_status' => EmploymentStatus::Inactive,
                'updated_by' => $actor->id,
            ]);
            $employee->delete();

            $employee->user->forceFill([
                'status' => UserStatus::Inactive,
                'deactivated_at' => now(),
                'deactivated_by' => $actor->id,
            ])->save();

            $this->auditLog->record($actor, 'archived', 'employees', $employee, 'Employee archived and linked account deactivated.', null, null, request());
        });
    }

    public function restore(Employee $employee, User $actor): void
    {
        DB::transaction(function () use ($employee, $actor): void {
            $employee->restore();
            $employee->update(['updated_by' => $actor->id]);

            $this->auditLog->record($actor, 'restored', 'employees', $employee, 'Employee restored. Linked account status was not automatically activated.', null, null, request());
        });
    }

    public function updateEmploymentStatus(Employee $employee, string $status, User $actor): Employee
    {
        return DB::transaction(function () use ($employee, $status, $actor): Employee {
            $old = ['employment_status' => $employee->employment_status->value];
            $employee->update(['employment_status' => $status, 'updated_by' => $actor->id]);

            $this->auditLog->record($actor, 'status_changed', 'employees', $employee, 'Employment status changed.', $old, [
                'employment_status' => $status,
            ], request());

            return $employee;
        });
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function employeeData(array $data): array
    {
        return collect($data)->only([
            'department_id', 'first_name', 'middle_name', 'last_name', 'suffix', 'date_of_birth', 'sex',
            'civil_status', 'email', 'contact_number', 'address_line_1', 'address_line_2', 'barangay',
            'city_municipality', 'province', 'postal_code', 'position', 'employment_type', 'employment_status',
            'hire_date', 'license_number', 'license_expiration_date', 'specialization', 'consultation_fee',
            'maximum_appointments_per_day', 'clinic_room', 'work_schedule_notes', 'emergency_contact_name',
            'emergency_contact_relationship', 'emergency_contact_number',
        ])->all();
    }
}
