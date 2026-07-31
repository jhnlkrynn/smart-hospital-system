<section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-gray-950">Existing Conditions</h3>
    <div class="mt-4 grid gap-3">
        @forelse($patient->conditions as $condition)
            <div class="rounded-md border border-gray-200 p-4 text-sm">
                <div class="font-semibold text-gray-950">{{ $condition->condition_name }}</div>
                <div class="text-gray-600">{{ str($condition->status->value)->title() }}</div>
            </div>
        @empty
            <p class="text-sm text-gray-600">No existing conditions recorded.</p>
        @endforelse
    </div>
    @can('patients.manage-conditions')
        <form method="POST" action="{{ route('admin.patients.conditions.store', $patient) }}" class="mt-5 grid gap-3 md:grid-cols-4">
            @csrf
            <input name="condition_name" placeholder="Condition" class="rounded-md border-gray-300">
            <select name="status" class="rounded-md border-gray-300">@foreach($conditionStatuses as $status)<option value="{{ $status->value }}">{{ str($status->value)->title() }}</option>@endforeach</select>
            <input name="diagnosis_date" type="date" class="rounded-md border-gray-300">
            <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Add Condition</button>
        </form>
    @endcan
</section>
