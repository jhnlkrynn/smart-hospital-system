<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-2xl font-semibold text-gray-950">Employees</h2>
            @can('employees.create')<a href="{{ route('admin.employees.create') }}" class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Add Employee</a>@endcan
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-alert :message="session('status')" />
            <form class="grid gap-3 rounded-lg border border-gray-200 bg-white p-4 md:grid-cols-4 xl:grid-cols-8">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search employees" class="rounded-md border-gray-300 xl:col-span-2">
                <select name="department_id" class="rounded-md border-gray-300"><option value="">All departments</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(($filters['department_id'] ?? '') == $department->id)>{{ $department->name }}</option>@endforeach</select>
                <select name="role" class="rounded-md border-gray-300"><option value="">All roles</option>@foreach($roles as $role)<option value="{{ $role->name }}" @selected(($filters['role'] ?? '') === $role->name)>{{ str($role->name)->replace('-', ' ')->title() }}</option>@endforeach</select>
                <select name="employment_status" class="rounded-md border-gray-300"><option value="">Employment</option>@foreach($employmentStatuses as $status)<option value="{{ $status->value }}" @selected(($filters['employment_status'] ?? '') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
                <select name="employment_type" class="rounded-md border-gray-300"><option value="">Type</option>@foreach($employmentTypes as $type)<option value="{{ $type->value }}" @selected(($filters['employment_type'] ?? '') === $type->value)>{{ $type->label() }}</option>@endforeach</select>
                <select name="account_status" class="rounded-md border-gray-300"><option value="">Account</option>@foreach($accountStatuses as $status)<option value="{{ $status->value }}" @selected(($filters['account_status'] ?? '') === $status->value)>{{ str($status->value)->replace('_', ' ')->title() }}</option>@endforeach</select>
                <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Filter</button>
            </form>
            @if($employees->isEmpty())
                <x-empty-state title="No employees found" message="Create an employee or adjust your filters." />
            @else
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                                <tr><th class="px-4 py-3">Photo</th><th class="px-4 py-3">Employee Number</th><th class="px-4 py-3">Name</th><th class="px-4 py-3">Role</th><th class="px-4 py-3">Department</th><th class="px-4 py-3">Position</th><th class="px-4 py-3">Employment</th><th class="px-4 py-3">Account</th><th class="px-4 py-3">Actions</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($employees as $employee)
                                    <tr class="{{ $employee->trashed() ? 'bg-gray-50 text-gray-500' : '' }}">
                                        <td class="px-4 py-3"><x-profile-avatar :employee="$employee" /></td>
                                        <td class="px-4 py-3 font-semibold">{{ $employee->employee_number }}</td>
                                        <td class="px-4 py-3">{{ $employee->full_name }}</td>
                                        <td class="px-4 py-3">{{ str($employee->user->roles->first()?->name ?? 'none')->replace('-', ' ')->title() }}</td>
                                        <td class="px-4 py-3">{{ $employee->department?->name }}</td>
                                        <td class="px-4 py-3">{{ $employee->position }}</td>
                                        <td class="px-4 py-3"><x-status-badge :status="$employee->employment_status->value" /></td>
                                        <td class="px-4 py-3"><x-status-badge :status="$employee->user->status->value" /></td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-3">
                                                <a class="text-sm font-medium text-gray-900" href="{{ route('admin.employees.show', $employee) }}">View</a>
                                                @if(!$employee->trashed())
                                                    @can('update', $employee)<a class="text-sm font-medium text-blue-700" href="{{ route('admin.employees.edit', $employee) }}">Edit</a>@endcan
                                                    @can('delete', $employee)<x-confirmation-modal :action="route('admin.employees.destroy', $employee)" title="Archive employee" message="This will archive the employee and deactivate the linked user account. Continue?">Archive</x-confirmation-modal>@endcan
                                                @else
                                                    @can('restore', $employee)<form method="POST" action="{{ route('admin.employees.restore', $employee->id) }}">@csrf @method('PATCH')<button class="text-sm font-medium text-emerald-700">Restore</button></form>@endcan
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                {{ $employees->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
