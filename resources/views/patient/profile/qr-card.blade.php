<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">My QR Patient Card</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">@include('admin.patients.partials.qr-card')</div></div>
</x-app-layout>
