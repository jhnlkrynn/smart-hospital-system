<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">Appointment Types</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
        <x-alert :message="session('status')" />
        <div class="flex justify-between"><a href="{{ route('admin.appointment-types.create') }}" class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Add Type</a><a href="{{ route('admin.appointment-types.index', ['archived' => request()->boolean('archived') ? 0 : 1]) }}" class="text-sm text-gray-600">Toggle archived</a></div>
        <div class="overflow-hidden rounded-lg border bg-white shadow-sm"><table class="min-w-full divide-y divide-gray-200 text-sm"><thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Code</th><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Duration</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead><tbody class="divide-y">@forelse($types as $type)<tr><td class="px-4 py-3 font-semibold">{{ $type->code }}</td><td class="px-4 py-3">{{ $type->name }}</td><td class="px-4 py-3">{{ $type->default_duration_minutes }} min</td><td class="px-4 py-3">{{ $type->is_active ? 'Active' : 'Inactive' }}</td><td class="px-4 py-3 text-right"><a href="{{ route('admin.appointment-types.show', $type) }}" class="text-gray-900 underline">View</a></td></tr>@empty<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No appointment types found.</td></tr>@endforelse</tbody></table></div>
        {{ $types->links() }}
    </div></div>
</x-app-layout>
