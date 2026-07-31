<section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-gray-950">Emergency Contacts</h3>
    <div class="mt-4 grid gap-3 md:grid-cols-2">
        @forelse($patient->emergencyContacts as $contact)
            <div class="rounded-md border border-gray-200 p-4 text-sm">
                <div class="font-semibold text-gray-950">{{ $contact->name }} @if($contact->is_primary)<span class="text-emerald-700">(Primary)</span>@endif</div>
                <div class="text-gray-600">{{ $contact->relationship }} · {{ $contact->contact_number }}</div>
            </div>
        @empty
            <p class="text-sm text-gray-600">No emergency contacts recorded.</p>
        @endforelse
    </div>
    @can('patients.manage-emergency-contacts')
        <form method="POST" action="{{ route('admin.patients.emergency-contacts.store', $patient) }}" class="mt-5 grid gap-3 md:grid-cols-5">
            @csrf
            <input name="name" placeholder="Name" class="rounded-md border-gray-300">
            <input name="relationship" placeholder="Relationship" class="rounded-md border-gray-300">
            <input name="contact_number" placeholder="Contact number" class="rounded-md border-gray-300">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_primary" value="1" class="rounded border-gray-300"> Primary</label>
            <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Add Contact</button>
        </form>
    @endcan
</section>
