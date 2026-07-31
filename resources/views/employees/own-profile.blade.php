<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">My Employment Profile</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-6 md:flex-row">
                    <x-profile-avatar :employee="$employee" size="h-24 w-24" />
                    <dl class="grid flex-1 gap-5 md:grid-cols-2">
                        <div><dt class="text-sm text-gray-500">Employee Number</dt><dd class="font-semibold text-gray-950">{{ $employee->employee_number }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Name</dt><dd class="font-semibold text-gray-950">{{ $employee->full_name }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Department</dt><dd class="font-semibold text-gray-950">{{ $employee->department?->name }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Position</dt><dd class="font-semibold text-gray-950">{{ $employee->position }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Employment Status</dt><dd><x-status-badge :status="$employee->employment_status->value" /></dd></div>
                        <div><dt class="text-sm text-gray-500">Work Schedule Notes</dt><dd class="font-semibold text-gray-950">{{ $employee->work_schedule_notes ?? 'Not set' }}</dd></div>
                    </dl>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
