@php
    $dashboardRoute = auth()->check() ? app(\App\Services\Auth\RoleRedirectService::class)->redirectPathFor(auth()->user()) : route('login');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-950">Access Denied</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-red-200 bg-white p-6 shadow-sm">
                <h1 class="text-lg font-semibold text-gray-950">You do not have permission to view this page.</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">This area is protected by role and permission checks.</p>
                <a href="{{ $dashboardRoute }}" class="mt-5 inline-flex rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                    Back to dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
