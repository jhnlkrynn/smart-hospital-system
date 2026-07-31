<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">My Patient Profile</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
        <x-alert :message="session('status')" />
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex gap-6"><img src="{{ $patient->profile_photo_url }}" class="h-24 w-24 rounded-full object-cover"><dl class="grid flex-1 gap-5 md:grid-cols-2"><div><dt class="text-sm text-gray-500">Patient Number</dt><dd class="font-semibold">{{ $patient->patient_number }}</dd></div><div><dt class="text-sm text-gray-500">Name</dt><dd class="font-semibold">{{ $patient->full_name }}</dd></div><div><dt class="text-sm text-gray-500">Age / Sex</dt><dd class="font-semibold">{{ $patient->age }} · {{ $patient->sex->label() }}</dd></div><div><dt class="text-sm text-gray-500">Contact</dt><dd class="font-semibold">{{ $patient->contact_number ?? 'Not set' }}</dd></div></dl></div>
            <div class="mt-6 flex gap-3"><a href="{{ route('patient.profile.edit') }}" class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Edit Allowed Fields</a><a href="{{ route('patient.qr-card') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium">View QR Card</a></div>
        </section>
    </div></div>
</x-app-layout>
