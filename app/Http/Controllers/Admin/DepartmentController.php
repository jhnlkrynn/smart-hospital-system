<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DepartmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Services\Department\DepartmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Department::class);

        $sort = in_array($request->input('sort'), ['name', 'code', 'created_at', 'updated_at'], true)
            ? $request->input('sort')
            : 'name';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $query = Department::query()
            ->with(['departmentHead', 'employees'])
            ->withCount('employees')
            ->search($request->input('search'));

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->input('archived') === 'only') {
            $query->onlyTrashed();
        } elseif ($request->input('archived') === 'with') {
            $query->withTrashed();
        }

        return view('admin.departments.index', [
            'departments' => $query->orderBy($sort, $direction)->paginate(10)->withQueryString(),
            'statuses' => DepartmentStatus::cases(),
            'filters' => $request->only(['search', 'status', 'archived', 'sort', 'direction']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Department::class);

        return view('admin.departments.create', [
            'department' => new Department(['status' => DepartmentStatus::Active]),
            'heads' => Employee::active()->orderBy('last_name')->get(),
            'statuses' => DepartmentStatus::cases(),
        ]);
    }

    public function store(StoreDepartmentRequest $request, DepartmentService $departments): RedirectResponse
    {
        $department = $departments->create($request->validated(), $request->user());

        return redirect()->route('admin.departments.show', $department)->with('status', 'Department created successfully.');
    }

    public function show(Department $department): View
    {
        $this->authorize('view', $department);

        $department->load(['departmentHead', 'createdBy', 'updatedBy', 'employees.user.roles']);

        return view('admin.departments.show', ['department' => $department]);
    }

    public function edit(Department $department): View
    {
        $this->authorize('update', $department);

        return view('admin.departments.edit', [
            'department' => $department,
            'heads' => Employee::active()->where('department_id', $department->id)->orderBy('last_name')->get(),
            'statuses' => DepartmentStatus::cases(),
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department, DepartmentService $departments): RedirectResponse
    {
        $departments->update($department, $request->validated(), $request->user());

        return redirect()->route('admin.departments.show', $department)->with('status', 'Department updated successfully.');
    }

    public function destroy(Department $department, DepartmentService $departments): RedirectResponse
    {
        $this->authorize('delete', $department);
        $departments->archive($department, request()->user());

        return redirect()->route('admin.departments.index')->with('status', 'Department archived successfully.');
    }

    public function restore(int $department, DepartmentService $departments): RedirectResponse
    {
        $department = Department::onlyTrashed()->findOrFail($department);
        $this->authorize('restore', $department);
        $departments->restore($department, request()->user());

        return redirect()->route('admin.departments.show', $department)->with('status', 'Department restored successfully.');
    }
}
