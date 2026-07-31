@props([
    'title',
    'subtitle',
    'cards' => [],
    'primary' => null,
])

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ $currentDate }}</p>
                <h2 class="text-2xl font-semibold leading-tight text-gray-950">{{ $title }}</h2>
            </div>
            <x-status-badge :status="$accountStatus" />
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-alert type="success" :message="session('status')" />

            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="grid gap-6 lg:grid-cols-[1fr_280px] lg:items-start">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ $roleLabel }}</p>
                        <h1 class="mt-2 text-2xl font-bold text-gray-950">Welcome, {{ $user->name }}</h1>
                        <p class="mt-3 max-w-3xl text-sm leading-6 text-gray-600">{{ $subtitle }}</p>
                    </div>
                    <dl class="grid gap-3 rounded-lg bg-gray-50 p-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">Current role</dt>
                            <dd class="mt-1 text-gray-950">{{ $roleLabel }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Last login</dt>
                            <dd class="mt-1 text-gray-950">{{ $lastLogin }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Unread notifications</dt>
                            <dd class="mt-1 text-gray-950">{{ $unreadNotifications }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            @if ($primary)
                <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-950">{{ $primary['title'] }}</h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600">{{ $primary['description'] }}</p>
                </section>
            @endif

            <section>
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-950">Accessible Work Areas</h2>
                    <span class="text-sm text-gray-500">Phase 3 access foundation</span>
                </div>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($cards as $card)
                        <x-dashboard-card :title="$card['title']" :description="$card['description']" />
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Implementation Progress</h2>
                <ul class="mt-4 grid gap-3 text-sm text-gray-700 sm:grid-cols-2">
                    @foreach ($progressItems as $item)
                        <li class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>
</x-app-layout>
