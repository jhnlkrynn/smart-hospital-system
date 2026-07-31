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

            @if (! empty($appointmentMetrics))
                <section>
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h2 class="text-lg font-semibold text-gray-950">Appointment Metrics</h2>
                        <span class="text-sm text-gray-500">Live data</span>
                    </div>
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Today</dt><dd class="mt-2 text-2xl font-semibold">{{ $appointmentMetrics['appointments_today'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Pending</dt><dd class="mt-2 text-2xl font-semibold">{{ $appointmentMetrics['pending_requests'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Approved Today</dt><dd class="mt-2 text-2xl font-semibold">{{ $appointmentMetrics['approved_today'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Upcoming</dt><dd class="mt-2 text-2xl font-semibold">{{ $appointmentMetrics['upcoming'] }}</dd></div>
                    </div>
                </section>
            @endif

            @if (! empty($queueMetrics))
                <section>
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h2 class="text-lg font-semibold text-gray-950">Queue Metrics</h2>
                        <span class="text-sm text-gray-500">Today</span>
                    </div>
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Waiting</dt><dd class="mt-2 text-2xl font-semibold">{{ $queueMetrics['waiting'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Called</dt><dd class="mt-2 text-2xl font-semibold">{{ $queueMetrics['called'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Triaged</dt><dd class="mt-2 text-2xl font-semibold">{{ $queueMetrics['triaged'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Completed</dt><dd class="mt-2 text-2xl font-semibold">{{ $queueMetrics['completed'] }}</dd></div>
                    </div>
                </section>
            @endif

            @if (! empty($consultationMetrics))
                <section>
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h2 class="text-lg font-semibold text-gray-950">Consultation Metrics</h2>
                        <span class="text-sm text-gray-500">Clinical records</span>
                    </div>
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">In Progress</dt><dd class="mt-2 text-2xl font-semibold">{{ $consultationMetrics['in_progress'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Completed Today</dt><dd class="mt-2 text-2xl font-semibold">{{ $consultationMetrics['completed_today'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Completed Total</dt><dd class="mt-2 text-2xl font-semibold">{{ $consultationMetrics['completed_total'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Issued Certificates</dt><dd class="mt-2 text-2xl font-semibold">{{ $consultationMetrics['certificates_issued'] }}</dd></div>
                    </div>
                </section>
            @endif

            @if (! empty($laboratoryMetrics))
                <section>
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h2 class="text-lg font-semibold text-gray-950">Laboratory Metrics</h2>
                        <span class="text-sm text-gray-500">Live workflow</span>
                    </div>
                    <div class="grid gap-4 md:grid-cols-5">
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">New Today</dt><dd class="mt-2 text-2xl font-semibold">{{ $laboratoryMetrics['new_requests_today'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Specimen Pending</dt><dd class="mt-2 text-2xl font-semibold">{{ $laboratoryMetrics['specimen_pending'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">In Process</dt><dd class="mt-2 text-2xl font-semibold">{{ $laboratoryMetrics['in_process'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Released Today</dt><dd class="mt-2 text-2xl font-semibold">{{ $laboratoryMetrics['released_today'] }}</dd></div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm"><dt class="text-sm text-gray-500">Critical Open</dt><dd class="mt-2 text-2xl font-semibold">{{ $laboratoryMetrics['critical_open'] }}</dd></div>
                    </div>
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
