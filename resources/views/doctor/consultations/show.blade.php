<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">{{ $consultation->consultation_number }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-alert type="success" :message="session('status')" />
                <section class="rounded-lg border bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between"><h3 class="text-lg font-semibold">Clinical Notes</h3><x-status-badge :status="$consultation->status->value" /></div>
                    <form method="POST" action="{{ route('doctor.consultations.update', $consultation) }}" class="space-y-4">
                        @csrf @method('PUT')
                        @foreach(['subjective_notes' => 'Subjective', 'objective_notes' => 'Objective', 'assessment' => 'Assessment', 'clinical_impression' => 'Clinical impression', 'treatment_plan' => 'Treatment plan', 'follow_up_instructions' => 'Follow-up instructions', 'internal_doctor_notes' => 'Internal doctor notes', 'patient_summary' => 'Patient summary'] as $field => $label)
                            <div>
                                <x-input-label :value="$label" />
                                <textarea name="{{ $field }}" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old($field, $consultation->{$field}) }}</textarea>
                                <x-input-error :messages="$errors->get($field)" class="mt-1" />
                            </div>
                        @endforeach
                        <div>
                            <x-input-label value="Follow-up date" />
                            <x-text-input type="date" name="follow_up_date" value="{{ old('follow_up_date', optional($consultation->follow_up_date)->format('Y-m-d')) }}" />
                        </div>
                        @if($consultation->status->isEditable())
                            <div class="flex gap-3"><x-primary-button>Save</x-primary-button></div>
                        @endif
                    </form>
                    @if($consultation->status->isEditable())
                        <form method="POST" action="{{ route('doctor.consultations.complete', $consultation) }}" class="mt-3">
                            @csrf
                            <input type="hidden" name="clinical_impression" value="{{ old('clinical_impression', $consultation->clinical_impression) }}">
                            <input type="hidden" name="treatment_plan" value="{{ old('treatment_plan', $consultation->treatment_plan) }}">
                            <input type="hidden" name="follow_up_instructions" value="{{ old('follow_up_instructions', $consultation->follow_up_instructions) }}">
                            <x-secondary-button>Complete Consultation</x-secondary-button>
                        </form>
                    @endif
                </section>
                <section class="rounded-lg border bg-white p-5 shadow-sm">
                    <h3 class="mb-4 text-lg font-semibold">Diagnoses</h3>
                    <div class="space-y-2">
                        @forelse($consultation->diagnoses as $diagnosis)
                            <div class="rounded-md border p-3 text-sm"><span class="font-semibold">{{ $diagnosis->diagnosis_name_snapshot }}</span> <span class="text-gray-500">{{ $diagnosis->diagnosis_type->label() }} / {{ $diagnosis->diagnosis_status->label() }}</span></div>
                        @empty
                            <p class="text-sm text-gray-500">No diagnoses recorded.</p>
                        @endforelse
                    </div>
                    @if($consultation->status->isEditable())
                        <form method="POST" action="{{ route('doctor.consultations.diagnoses.store', $consultation) }}" class="mt-4 grid gap-3 md:grid-cols-2">
                            @csrf
                            <select name="diagnosis_catalog_id" class="rounded-md border-gray-300"><option value="">Custom diagnosis</option>@foreach($catalog as $item)<option value="{{ $item->id }}">{{ $item->code }} {{ $item->name }}</option>@endforeach</select>
                            <x-text-input name="diagnosis_name" placeholder="Custom diagnosis name" />
                            <select name="diagnosis_type" class="rounded-md border-gray-300"><option value="primary">Primary</option><option value="secondary">Secondary</option><option value="provisional">Provisional</option><option value="differential">Differential</option></select>
                            <select name="diagnosis_status" class="rounded-md border-gray-300"><option value="active">Active</option><option value="chronic">Chronic</option><option value="resolved">Resolved</option><option value="ruled_out">Ruled out</option></select>
                            <textarea name="clinical_notes" class="rounded-md border-gray-300 md:col-span-2" placeholder="Clinical notes"></textarea>
                            <label class="text-sm"><input type="checkbox" name="sync_to_problem_list" value="1" checked> Sync to problem list</label>
                            <x-primary-button>Add Diagnosis</x-primary-button>
                        </form>
                    @endif
                </section>
            </div>
            <aside class="space-y-6">
                <section class="rounded-lg border bg-white p-5 shadow-sm"><h3 class="font-semibold">Patient</h3><p>{{ $consultation->patient?->full_name }}</p><p class="text-sm text-gray-500">{{ $consultation->patient?->patient_number }}</p>@foreach($consultation->patient?->allergies ?? [] as $allergy)<p class="mt-2 rounded bg-red-50 p-2 text-sm text-red-800">Allergy: {{ $allergy->allergen }}</p>@endforeach</section>
                <section class="rounded-lg border bg-white p-5 shadow-sm"><h3 class="font-semibold">Triage</h3><p class="text-sm">{{ $consultation->queue?->triageRecord?->chief_complaint ?? 'No triage record.' }}</p><p class="text-sm text-gray-500">Pain: {{ $consultation->queue?->triageRecord?->pain_scale ?? 'N/A' }}</p></section>
                <section class="rounded-lg border bg-white p-5 shadow-sm"><h3 class="font-semibold">Attachments</h3><form method="POST" enctype="multipart/form-data" action="{{ route('doctor.consultations.attachments.store', $consultation) }}" class="mt-3 space-y-2">@csrf<x-text-input name="title" placeholder="Title" class="w-full" /><input type="file" name="file" class="w-full text-sm"><x-secondary-button>Upload</x-secondary-button></form></section>
                <section class="rounded-lg border bg-white p-5 shadow-sm"><h3 class="font-semibold">Laboratory</h3><a class="mt-3 inline-flex rounded-md border px-3 py-2 text-sm" href="{{ route('doctor.consultations.laboratory-requests.create', $consultation) }}">Request Tests</a></section>
                <section class="rounded-lg border bg-white p-5 shadow-sm"><h3 class="font-semibold">Medical Certificate</h3><form method="POST" action="{{ route('doctor.consultations.certificates.store', $consultation) }}" class="mt-3 space-y-2">@csrf<x-text-input name="purpose" placeholder="Purpose" class="w-full" /><textarea name="clinical_summary" placeholder="Clinical summary" class="w-full rounded-md border-gray-300"></textarea><textarea name="recommendation" placeholder="Recommendation" class="w-full rounded-md border-gray-300"></textarea><x-secondary-button>Create Draft</x-secondary-button></form></section>
            </aside>
        </div>
    </div>
</x-app-layout>
