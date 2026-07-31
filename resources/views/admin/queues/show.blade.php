<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">{{ $queue->queue_number }}</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-5xl space-y-4 px-4">
        <x-alert :message="session('status')" />
        <section class="rounded-lg border bg-white p-6 shadow-sm">
            <dl class="grid gap-4 md:grid-cols-3">
                <div><dt class="text-sm text-gray-500">Patient</dt><dd class="font-semibold">{{ $queue->patient?->full_name }}</dd></div>
                <div><dt class="text-sm text-gray-500">Priority</dt><dd>{{ $queue->priority_label }}</dd></div>
                <div><dt class="text-sm text-gray-500">Status</dt><dd><x-status-badge :status="$queue->status->value" /></dd></div>
                <div><dt class="text-sm text-gray-500">Department</dt><dd>{{ $queue->department?->name }}</dd></div>
                <div><dt class="text-sm text-gray-500">Doctor</dt><dd>{{ $queue->doctor?->full_name ?? 'Unassigned' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Waiting</dt><dd>{{ $queue->waiting_minutes }} minutes</dd></div>
            </dl>
            @if($queue->patient?->allergies?->where('is_active', true)->count())
                <div class="mt-4 rounded-md bg-red-50 p-3 text-sm font-semibold text-red-700">Active allergy warning: {{ $queue->patient->allergies->where('is_active', true)->pluck('allergen')->implode(', ') }}</div>
            @endif
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('nurse.triage.create', $queue) }}" class="rounded-md bg-gray-950 px-4 py-2 text-sm text-white">Record Triage</a>
                <form method="POST" action="{{ route('admin.queues.doctor', $queue) }}">@csrf<button class="rounded-md border px-4 py-2 text-sm">Send to Doctor</button></form>
                <form method="POST" action="{{ route('admin.queues.complete', $queue) }}">@csrf<button class="rounded-md border px-4 py-2 text-sm">Complete</button></form>
                <form method="POST" action="{{ route('admin.queues.skip', $queue) }}">@csrf<button class="rounded-md border px-4 py-2 text-sm">Skip</button></form>
                <form method="POST" action="{{ route('admin.queues.cancel', $queue) }}">@csrf<button class="rounded-md border px-4 py-2 text-sm">Cancel</button></form>
            </div>
        </section>
        @if($queue->triageRecord)
            <section class="rounded-lg border bg-white p-6 shadow-sm"><h3 class="font-semibold">Triage</h3><p class="mt-2 text-sm">{{ $queue->triageRecord->chief_complaint }} | Pain {{ $queue->triageRecord->pain_scale }}/10 | {{ $queue->triageRecord->acuity->label() }} | Fall risk {{ $queue->triageRecord->fall_risk_level }}</p></section>
        @endif
        <section class="rounded-lg border bg-white p-6 shadow-sm"><h3 class="font-semibold">History</h3><ul class="mt-3 space-y-2 text-sm">@foreach($queue->histories as $history)<li>{{ $history->created_at->format('M d, Y h:i A') }}: {{ $history->old_status ?? 'new' }} to {{ $history->new_status }} by {{ $history->changedBy?->name ?? 'System' }}</li>@endforeach</ul></section>
    </div></div>
</x-app-layout>
