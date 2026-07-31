<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-2xl font-semibold text-gray-950">Patients</h2>
            @can('patients.create')<a href="{{ route('admin.patients.create') }}" class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Add Patient</a>@endcan
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-alert :message="session('status')" />
            <form class="grid gap-3 rounded-lg border border-gray-200 bg-white p-4 md:grid-cols-6">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search patients" class="rounded-md border-gray-300 md:col-span-2">
                <select name="status" class="rounded-md border-gray-300"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
                <select name="sex" class="rounded-md border-gray-300"><option value="">All sex</option>@foreach($sexes as $sex)<option value="{{ $sex->value }}" @selected(($filters['sex'] ?? '') === $sex->value)>{{ $sex->label() }}</option>@endforeach</select>
                <select name="account" class="rounded-md border-gray-300"><option value="">Any account</option><option value="linked" @selected(($filters['account'] ?? '') === 'linked')>Linked</option><option value="unlinked" @selected(($filters['account'] ?? '') === 'unlinked')>No account</option></select>
                <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Filter</button>
            </form>
            @if($patients->isEmpty())
                <x-empty-state title="No patients found" message="Register a patient or adjust your filters." />
            @else
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500"><tr><th class="px-4 py-3">Photo</th><th class="px-4 py-3">Patient Number</th><th class="px-4 py-3">Name</th><th class="px-4 py-3">Age</th><th class="px-4 py-3">Sex</th><th class="px-4 py-3">Contact</th><th class="px-4 py-3">Account</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Registered</th><th class="px-4 py-3">Actions</th></tr></thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($patients as $patient)
                                    <tr class="{{ $patient->trashed() ? 'bg-gray-50 text-gray-500' : '' }}">
                                        <td class="px-4 py-3"><img src="{{ $patient->profile_photo_url }}" class="h-10 w-10 rounded-full object-cover"></td>
                                        <td class="px-4 py-3 font-semibold">{{ $patient->patient_number }}</td>
                                        <td class="px-4 py-3">{{ $patient->full_name }}</td>
                                        <td class="px-4 py-3">{{ $patient->age }}</td>
                                        <td class="px-4 py-3">{{ $patient->sex->label() }}</td>
                                        <td class="px-4 py-3">{{ $patient->contact_number ?? 'Not set' }}</td>
                                        <td class="px-4 py-3">{{ $patient->user ? 'Linked' : 'No account' }}</td>
                                        <td class="px-4 py-3"><x-status-badge :status="$patient->status->value" /></td>
                                        <td class="px-4 py-3">{{ $patient->registration_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-3"><div class="flex gap-3"><a href="{{ route('admin.patients.show', $patient) }}" class="font-medium text-gray-900">View</a>@if(!$patient->trashed())@can('update', $patient)<a href="{{ route('admin.patients.edit', $patient) }}" class="font-medium text-blue-700">Edit</a>@endcan @can('delete', $patient)<x-confirmation-modal :action="route('admin.patients.destroy', $patient)" title="Archive patient" message="This preserves patient history and removes the patient from active lists. Continue?">Archive</x-confirmation-modal>@endcan @else @can('restore', $patient)<form method="POST" action="{{ route('admin.patients.restore', $patient->id) }}">@csrf @method('PATCH')<button class="font-medium text-emerald-700">Restore</button></form>@endcan @endif</div></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                {{ $patients->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
