<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\Sex;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Services\Audit\AuditLogService;
use App\Services\Employee\EmployeeService;
use App\Support\AccessControl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        $sort = in_array($request->input('sort'), ['last_name', 'employee_number', 'hire_date', 'created_at', 'updated_at'], true)
            ? $request->input('sort')
            : 'last_name';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $query = Employee::query()
            ->with(['user.roles', 'department'])
            ->search($request->input('search'))
            ->byDepartment($request->input('department_id'))
            ->byEmploymentStatus($request->input('employment_status'));

        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->input('employment_type'));
        }

        if ($request->filled('account_status')) {
            $query->whereHas('user', fn ($query) => $query->where('status', $request->input('account_status')));
        }

        if ($request->filled('role')) {
            $query->whereHas('user.roles', fn ($query) => $query->where('name', $request->input('role')));
        }

        if ($request->input('archived') === 'only') {
            $query->onlyTrashed();
        } elseif ($request->input('archived') === 'with') {
            $query->withTrashed();
        }

        return view('admin.employees.index', [
            'employees' => $query->orderBy($sort, $direction)->paginate(10)->withQueryString(),
            'departments' => Department::orderBy('name')->get(),
            'roles' => Role::whereIn('name', $this->assignableRoles($request->user()))->orderBy('name')->get(),
            'employmentStatuses' => EmploymentStatus::cases(),
            'employmentTypes' => EmploymentType::cases(),
            'accountStatuses' => UserStatus::cases(),
            'filters' => $request->only(['search', 'department_id', 'role', 'employment_status', 'employment_type', 'account_status', 'archived', 'sort', 'direction']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Employee::class);

        return view('admin.employees.create', $this->formData($request));
    }

    public function store(StoreEmployeeRequest $request, EmployeeService $employees): RedirectResponse
    {
        $employee = $employees->create($request->validated(), $request->user());

        return redirect()->route('admin.employees.show', $employee)->with('status', 'Employee account created successfully.');
    }

    public function show(Employee $employee, AuditLogService $auditLog): View
    {
        $this->authorize('view', $employee);
        $employee->load(['user.roles', 'department', 'createdBy', 'updatedBy']);

        if ($employee->user_id !== request()->user()->id) {
            $auditLog->record(request()->user(), 'viewed', 'employees', $employee, 'Sensitive employee profile viewed.', null, null, request());
        }

        return view('admin.employees.show', ['employee' => $employee]);
    }

    public function edit(Request $request, Employee $employee): View
    {
        $this->authorize('update', $employee);

        return view('admin.employees.edit', $this->formData($request) + ['employee' => $employee->load('user.roles')]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee, EmployeeService $employees): RedirectResponse
    {
        $employees->update($employee, $request->validated(), $request->user());

        return redirect()->route('admin.employees.show', $employee)->with('status', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee, EmployeeService $employees): RedirectResponse
    {
        $this->authorize('delete', $employee);
        $employees->archive($employee, request()->user());

        return redirect()->route('admin.employees.index')->with('status', 'Employee archived and account deactivated.');
    }

    public function restore(int $employee, EmployeeService $employees): RedirectResponse
    {
        $employee = Employee::onlyTrashed()->with('department')->findOrFail($employee);
        $this->authorize('restore', $employee);
        $employees->restore($employee, request()->user());

        return redirect()->route('admin.employees.show', $employee)->with('status', 'Employee restored successfully.');
    }

    public function ownProfile(Request $request): View
    {
        $employee = $request->user()->employee()->with(['department', 'user.roles'])->firstOrFail();
        $this->authorize('view', $employee);

        return view('employees.own-profile', ['employee' => $employee]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request): array
    {
        return [
            'departments' => Department::active()->orderBy('name')->get(),
            'roles' => Role::whereIn('name', $this->assignableRoles($request->user()))->orderBy('name')->get(),
            'employmentStatuses' => EmploymentStatus::cases(),
            'employmentTypes' => EmploymentType::cases(),
            'sexes' => Sex::cases(),
            'accountStatuses' => UserStatus::cases(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function assignableRoles($user): array
    {
        $roles = ['hospital-admin', 'doctor', 'nurse', 'pharmacist', 'laboratory-staff', 'cashier'];

        if ($user->hasRole('super-admin')) {
            array_unshift($roles, 'super-admin');
        }

        return array_values(array_intersect($roles, array_keys(AccessControl::ROLES)));
    }
}
