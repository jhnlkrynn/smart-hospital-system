<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-2xl font-semibold text-gray-950">Departments</h2>
            @can('departments.create')
                <a href="{{ route('admin.departments.create') }}" class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Add Department</a>
            @endcan
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-alert :message="session('status')" />
            <form class="grid gap-3 rounded-lg border border-gray-200 bg-white p-4 md:grid-cols-6">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search departments" class="rounded-md border-gray-300 md:col-span-2">
                <select name="status" class="rounded-md border-gray-300">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <select name="archived" class="rounded-md border-gray-300">
                    <option value="">Active only</option>
                    <option value="with" @selected(($filters['archived'] ?? '') === 'with')>With archived</option>
                    <option value="only" @selected(($filters['archived'] ?? '') === 'only')>Archived only</option>
                </select>
                <select name="sort" class="rounded-md border-gray-300">
                    @foreach(['name' => 'Name', 'code' => 'Code', 'created_at' => 'Created', 'updated_at' => 'Updated'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'name') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Filter</button>
            </form>
            @if($departments->isEmpty())
                <x-empty-state title="No departments found" message="Create a department or adjust your filters." />
            @else
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                                <tr><th class="px-4 py-3">Code</th><th class="px-4 py-3">Department</th><th class="px-4 py-3">Location</th><th class="px-4 py-3">Department Head</th><th class="px-4 py-3">Employees</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Updated</th><th class="px-4 py-3">Actions</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($departments as $department)
                                    <tr class="{{ $department->trashed() ? 'bg-gray-50 text-gray-500' : '' }}">
                                        <td class="px-4 py-3 font-semibold">{{ $department->code }}</td>
                                        <td class="px-4 py-3">{{ $department->name }}</td>
                                        <td class="px-4 py-3">{{ $department->location ?? 'Not set' }}</td>
                                        <td class="px-4 py-3">{{ $department->departmentHead?->full_name ?? 'Not assigned' }}</td>
                                        <td class="px-4 py-3">{{ $department->employees_count }}</td>
                                        <td class="px-4 py-3"><x-status-badge :status="$department->status->value" /></td>
                                        <td class="px-4 py-3">{{ $department->updated_at->format('M d, Y') }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-3">
                                                <a class="text-sm font-medium text-gray-900" href="{{ route('admin.departments.show', $department) }}">View</a>
                                                @if(!$department->trashed())
                                                    @can('departments.update')<a class="text-sm font-medium text-blue-700" href="{{ route('admin.departments.edit', $department) }}">Edit</a>@endcan
                                                    @can('departments.archive')<x-confirmation-modal :action="route('admin.departments.destroy', $department)" title="Archive department" message="Departments with active employees cannot be archived. Continue?">Archive</x-confirmation-modal>@endcan
                                                @else
                                                    @can('departments.restore')
                                                        <form method="POST" action="{{ route('admin.departments.restore', $department->id) }}">@csrf @method('PATCH')<button class="text-sm font-medium text-emerald-700">Restore</button></form>
                                                    @endcan
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                {{ $departments->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
