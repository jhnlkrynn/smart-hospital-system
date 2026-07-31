<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">Consultations</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4">
            <x-alert type="success" :message="session('status')" />
            <form class="flex flex-wrap gap-3 rounded-lg border bg-white p-4 shadow-sm">
                <x-text-input name="search" value="{{ request('search') }}" placeholder="Search patient or consultation" class="w-72" />
                <select name="status" class="rounded-md border-gray-300">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <x-primary-button>Filter</x-primary-button>
            </form>
            <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                <table class="min-w-full divide-y text-sm">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Consultation</th><th class="px-4 py-3 text-left">Patient</th><th class="px-4 py-3 text-left">Department</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead>
                    <tbody class="divide-y">
                        @forelse($consultations as $consultation)
                            <tr>
                                <td class="px-4 py-3 font-semibold">{{ $consultation->consultation_number }}</td>
                                <td class="px-4 py-3">{{ $consultation->patient?->full_name }}</td>
                                <td class="px-4 py-3">{{ $consultation->department?->name }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$consultation->status->value" /></td>
                                <td class="px-4 py-3 text-right"><a class="text-indigo-700 underline" href="{{ route('doctor.consultations.show', $consultation) }}">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No consultations found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $consultations->links() }}
        </div>
    </div>
</x-app-layout>
