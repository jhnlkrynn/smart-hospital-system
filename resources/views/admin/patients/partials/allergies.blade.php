<section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-gray-950">Allergies</h3>
    <div class="mt-4 grid gap-3">
        @forelse($patient->allergies as $allergy)
            <div class="rounded-md border {{ $allergy->severity->value === 'severe' ? 'border-red-300 bg-red-50' : 'border-gray-200' }} p-4 text-sm">
                <div class="font-semibold text-gray-950">{{ $allergy->allergen }} · {{ str($allergy->severity->value)->title() }}</div>
                <div class="text-gray-600">{{ str($allergy->allergy_type->value)->title() }}{{ $allergy->reaction ? ' · '.$allergy->reaction : '' }}</div>
            </div>
        @empty
            <p class="text-sm text-gray-600">No allergies recorded.</p>
        @endforelse
    </div>
    @can('patients.manage-allergies')
        <form method="POST" action="{{ route('admin.patients.allergies.store', $patient) }}" class="mt-5 grid gap-3 md:grid-cols-5">
            @csrf
            <input name="allergen" placeholder="Allergen" class="rounded-md border-gray-300">
            <select name="allergy_type" class="rounded-md border-gray-300">@foreach($allergyTypes as $type)<option value="{{ $type->value }}">{{ str($type->value)->title() }}</option>@endforeach</select>
            <select name="severity" class="rounded-md border-gray-300">@foreach($allergySeverities as $severity)<option value="{{ $severity->value }}">{{ str($severity->value)->title() }}</option>@endforeach</select>
            <input name="reaction" placeholder="Reaction" class="rounded-md border-gray-300">
            <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Add Allergy</button>
        </form>
    @endcan
</section>
