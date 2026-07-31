<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">Waiting Queue</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl space-y-4 px-4">
        <x-alert :message="session('status')" />
        <div class="flex flex-wrap gap-3">
            @can('queues.manage')<a href="{{ route('admin.queues.create') }}" class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Add Walk-in</a>@endcan
            @foreach($departments as $department)
                @can('queues.call')<form method="POST" action="{{ route('admin.queues.call-next', $department) }}">@csrf<button class="rounded-md border px-3 py-2 text-sm">Call {{ $department->code }}</button></form>@endcan
            @endforeach
        </div>
        <form class="grid gap-3 rounded-lg border bg-white p-4 md:grid-cols-4">
            <select name="department_id" class="rounded-md border-gray-300"><option value="">All departments</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>@endforeach</select>
            <select name="status" class="rounded-md border-gray-300"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
            <input type="date" name="date" value="{{ request('date') }}" class="rounded-md border-gray-300">
            <button class="rounded-md border px-4 py-2 text-sm">Filter</button>
        </form>
        <div class="overflow-hidden rounded-lg border bg-white shadow-sm"><table class="min-w-full divide-y text-sm"><thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Queue</th><th class="px-4 py-3 text-left">Patient</th><th class="px-4 py-3 text-left">Priority</th><th class="px-4 py-3 text-left">Department</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Waiting</th><th></th></tr></thead><tbody class="divide-y">@forelse($queues as $queue)<tr><td class="px-4 py-3 font-semibold">{{ $queue->queue_number }}</td><td class="px-4 py-3">{{ $queue->patient?->full_name }}</td><td class="px-4 py-3">{{ $queue->priority_label }}</td><td class="px-4 py-3">{{ $queue->department?->code }}</td><td class="px-4 py-3"><x-status-badge :status="$queue->status->value" /></td><td class="px-4 py-3">{{ $queue->waiting_minutes }} min</td><td class="px-4 py-3 text-right"><a class="underline" href="{{ route('admin.queues.show', $queue) }}">View</a></td></tr>@empty<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No queue records found.</td></tr>@endforelse</tbody></table></div>
        {{ $queues->links() }}
    </div></div>
</x-app-layout>
