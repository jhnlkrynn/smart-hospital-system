<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">Patient Verification</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex gap-6"><img src="{{ $patient->profile_photo_url }}" class="h-20 w-20 rounded-full object-cover"><dl class="grid flex-1 gap-5 md:grid-cols-2"><div><dt class="text-sm text-gray-500">Patient Number</dt><dd class="font-semibold">{{ $patient->patient_number }}</dd></div><div><dt class="text-sm text-gray-500">Name</dt><dd class="font-semibold">{{ $patient->full_name }}</dd></div><div><dt class="text-sm text-gray-500">Status</dt><dd><x-status-badge :status="$patient->status->value" /></dd></div><div><dt class="text-sm text-gray-500">Future Check-In</dt><dd class="font-semibold">Upcoming appointment/queue feature</dd></div></dl></div>
        </section>
        @can('patients.manage-allergies')@include('admin.patients.partials.allergies', ['allergyTypes' => [], 'allergySeverities' => []])@endcan
    </div></div>
</x-app-layout>
