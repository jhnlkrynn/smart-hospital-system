<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">Edit Appointment Type</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8"><form method="POST" action="{{ route('admin.appointment-types.update', $type) }}" class="rounded-lg border bg-white p-6 shadow-sm">@method('PUT') @include('admin.appointment-types._form')</form></div></div>
</x-app-layout>
