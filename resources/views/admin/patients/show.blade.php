<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="text-2xl font-semibold text-gray-950">{{ $patient->full_name }}</h2><x-status-badge :status="$patient->status->value" /></div></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-alert :message="session('status')" />
            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-6 md:flex-row">
                    <img src="{{ $patient->profile_photo_url }}" class="h-24 w-24 rounded-full object-cover ring-1 ring-gray-200">
                    <dl class="grid flex-1 gap-5 md:grid-cols-3">
                        <div><dt class="text-sm text-gray-500">Patient Number</dt><dd class="font-semibold text-gray-950">{{ $patient->patient_number }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Age / Sex</dt><dd class="font-semibold text-gray-950">{{ $patient->age }} · {{ $patient->sex->label() }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Account</dt><dd class="font-semibold text-gray-950">{{ $patient->user ? 'Linked' : 'No online account' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Contact</dt><dd class="font-semibold text-gray-950">{{ $patient->contact_number ?? 'Not set' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Email</dt><dd class="font-semibold text-gray-950">{{ $patient->email ?? 'Not set' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Registered</dt><dd class="font-semibold text-gray-950">{{ $patient->registration_date->format('M d, Y') }}</dd></div>
                    </dl>
                </div>
                <div class="mt-6 flex gap-3">@can('update', $patient)<a href="{{ route('admin.patients.edit', $patient) }}" class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Edit</a>@endcan @can('update', $patient)<form method="POST" action="{{ route('admin.patients.regenerate-qr', $patient) }}">@csrf<button class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Regenerate QR</button></form>@endcan</div>
            </section>
            @include('admin.patients.partials.qr-card')
            @can('patients.manage-emergency-contacts')
                @include('admin.patients.partials.emergency-contacts')
            @endcan
            @can('patients.manage-allergies')
                @include('admin.patients.partials.allergies')
            @endcan
            @can('patients.manage-conditions')
                @include('admin.patients.partials.conditions')
            @endcan
            @canany(['patients.manage-documents', 'patients.download-documents'])
                @include('admin.patients.partials.documents')
            @endcanany
        </div>
    </div>
</x-app-layout>
