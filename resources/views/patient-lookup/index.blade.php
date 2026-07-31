<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">Patient QR Lookup</h2></x-slot>
    <div class="py-8"><form method="POST" action="{{ route('patient-lookup.store') }}" class="mx-auto max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">@csrf
        <p class="mb-4 text-sm text-gray-600">Enter or scan a secure QR token. QR lookup requires authorization and is audited.</p>
        <x-input-label for="token" value="QR Token" /><x-text-input id="token" name="token" class="mt-1 block w-full" required />
        <x-input-error class="mt-2" :messages="$errors->get('token')" />
        <button class="mt-4 rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Lookup</button>
    </form></div>
</x-app-layout>
