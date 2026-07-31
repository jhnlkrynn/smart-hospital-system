<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-gray-950">{{ $department->name }}</h2>
            <x-status-badge :status="$department->status->value" />
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-alert :message="session('status')" />
            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <dl class="grid gap-5 md:grid-cols-3">
                    <div><dt class="text-sm text-gray-500">Code</dt><dd class="font-semibold text-gray-950">{{ $department->code }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Location</dt><dd class="font-semibold text-gray-950">{{ $department->location ?? 'Not set' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Department Head</dt><dd class="font-semibold text-gray-950">{{ $department->departmentHead?->full_name ?? 'Not assigned' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Contact</dt><dd class="font-semibold text-gray-950">{{ $department->contact_number ?? 'Not set' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Email</dt><dd class="font-semibold text-gray-950">{{ $department->email ?? 'Not set' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Employees</dt><dd class="font-semibold text-gray-950">{{ $department->employees->count() }}</dd></div>
                </dl>
                <p class="mt-5 text-sm leading-6 text-gray-600">{{ $department->description ?? 'No description provided.' }}</p>
                <div class="mt-6 flex gap-3">
                    @can('update', $department)<a href="{{ route('admin.departments.edit', $department) }}" class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Edit</a>@endcan
                    <a href="{{ route('admin.departments.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Back</a>
                </div>
            </section>
            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-950">Active Employees</h3>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @forelse($department->employees->where('deleted_at', null) as $employee)
                        <a href="{{ route('admin.employees.show', $employee) }}" class="rounded-md border border-gray-200 p-4 text-sm hover:bg-gray-50">
                            <span class="font-semibold text-gray-950">{{ $employee->full_name }}</span>
                            <span class="block text-gray-600">{{ $employee->position }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-gray-600">No active employees in this department.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
