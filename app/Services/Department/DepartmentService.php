<?php

namespace App\Services\Department;

use App\Enums\DepartmentStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepartmentService
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data, User $actor): Department
    {
        return DB::transaction(function () use ($data, $actor): Department {
            $department = Department::create($this->departmentData($data) + [
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->auditLog->record($actor, 'created', 'departments', $department, 'Department created.', null, $department->toArray(), request());

            return $department;
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Department $department, array $data, User $actor): Department
    {
        return DB::transaction(function () use ($department, $data, $actor): Department {
            $old = $department->only(array_keys($this->departmentData($data)));

            $department->fill($this->departmentData($data));
            $department->updated_by = $actor->id;
            $department->save();

            $this->auditLog->record($actor, 'updated', 'departments', $department, 'Department updated.', $old, $department->fresh()->toArray(), request());

            return $department;
        });
    }

    public function archive(Department $department, User $actor): void
    {
        DB::transaction(function () use ($department, $actor): void {
            if ($this->hasActiveEmployees($department)) {
                throw ValidationException::withMessages([
                    'department' => 'This department has active employees. Reassign or deactivate them before archiving.',
                ]);
            }

            $department->update(['status' => DepartmentStatus::Inactive, 'updated_by' => $actor->id]);
            $department->delete();

            $this->auditLog->record($actor, 'archived', 'departments', $department, 'Department archived.', null, null, request());
        });
    }

    public function restore(Department $department, User $actor): void
    {
        DB::transaction(function () use ($department, $actor): void {
            $department->restore();
            $department->update(['status' => DepartmentStatus::Active, 'updated_by' => $actor->id]);

            $this->auditLog->record($actor, 'restored', 'departments', $department, 'Department restored.', null, null, request());
        });
    }

    public function assignHead(Department $department, ?Employee $employee, User $actor): Department
    {
        return DB::transaction(function () use ($department, $employee, $actor): Department {
            if ($employee && $employee->department_id !== $department->id) {
                throw ValidationException::withMessages([
                    'department_head_employee_id' => 'The department head must belong to this department.',
                ]);
            }

            $old = ['department_head_employee_id' => $department->department_head_employee_id];
            $department->update([
                'department_head_employee_id' => $employee?->id,
                'updated_by' => $actor->id,
            ]);

            $this->auditLog->record($actor, 'head_changed', 'departments', $department, 'Department head changed.', $old, [
                'department_head_employee_id' => $employee?->id,
            ], request());

            return $department;
        });
    }

    public function hasActiveEmployees(Department $department): bool
    {
        return $department->activeEmployees()->exists();
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function departmentData(array $data): array
    {
        return collect($data)
            ->only(['code', 'name', 'description', 'location', 'contact_number', 'email', 'department_head_employee_id', 'status'])
            ->all();
    }
}
