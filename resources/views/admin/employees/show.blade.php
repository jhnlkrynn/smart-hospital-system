<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-gray-950">{{ $employee->full_name }}</h2>
            <x-status-badge :status="$employee->employment_status->value" />
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-alert :message="session('status')" />
            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-6 md:flex-row">
                    <x-profile-avatar :employee="$employee" size="h-24 w-24" />
                    <dl class="grid flex-1 gap-5 md:grid-cols-3">
                        <div><dt class="text-sm text-gray-500">Employee Number</dt><dd class="font-semibold text-gray-950">{{ $employee->employee_number }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Role</dt><dd class="font-semibold text-gray-950">{{ str($employee->user->roles->first()?->name ?? 'none')->replace('-', ' ')->title() }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Department</dt><dd class="font-semibold text-gray-950">{{ $employee->department?->name }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Position</dt><dd class="font-semibold text-gray-950">{{ $employee->position }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Email</dt><dd class="font-semibold text-gray-950">{{ $employee->email }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Account Status</dt><dd><x-status-badge :status="$employee->user->status->value" /></dd></div>
                        <div><dt class="text-sm text-gray-500">Age</dt><dd class="font-semibold text-gray-950">{{ $employee->age ?? 'Not set' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Hire Date</dt><dd class="font-semibold text-gray-950">{{ $employee->hire_date?->format('M d, Y') ?? 'Not set' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Last Login</dt><dd class="font-semibold text-gray-950">{{ $employee->user->last_login_at?->format('M d, Y h:i A') ?? 'Not recorded' }}</dd></div>
                    </dl>
                </div>
                <div class="mt-6 flex gap-3">
                    @can('update', $employee)<a href="{{ route('admin.employees.edit', $employee) }}" class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Edit</a>@endcan
                    <a href="{{ route('admin.employees.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Back</a>
                </div>
            </section>
            <section class="grid gap-6 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-gray-950">Professional Details</h3>
                    <dl class="mt-4 space-y-3 text-sm"><div><dt class="text-gray-500">License</dt><dd>{{ $employee->license_number ?? 'Not set' }}</dd></div><div><dt class="text-gray-500">Specialization</dt><dd>{{ $employee->specialization ?? 'Not set' }}</dd></div><div><dt class="text-gray-500">Clinic Room</dt><dd>{{ $employee->clinic_room ?? 'Not set' }}</dd></div></dl>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-gray-950">Emergency Contact</h3>
                    <dl class="mt-4 space-y-3 text-sm"><div><dt class="text-gray-500">Name</dt><dd>{{ $employee->emergency_contact_name ?? 'Not set' }}</dd></div><div><dt class="text-gray-500">Relationship</dt><dd>{{ $employee->emergency_contact_relationship ?? 'Not set' }}</dd></div><div><dt class="text-gray-500">Number</dt><dd>{{ $employee->emergency_contact_number ?? 'Not set' }}</dd></div></dl>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
