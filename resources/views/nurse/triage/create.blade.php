<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">Record Triage</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-5xl px-4">
        <form method="POST" action="{{ route('nurse.triage.store', $queue) }}" class="rounded-lg border bg-white p-6 shadow-sm">
            @csrf
            <div class="mb-4"><p class="font-semibold">{{ $queue->queue_number }} - {{ $queue->patient?->full_name }}</p>@if($queue->patient?->allergies?->where('is_active', true)->count())<p class="mt-2 rounded-md bg-red-50 p-3 text-sm font-semibold text-red-700">Allergies: {{ $queue->patient->allergies->where('is_active', true)->pluck('allergen')->implode(', ') }}</p>@endif</div>
            <div class="grid gap-4 md:grid-cols-3">
                <div class="md:col-span-2"><label class="text-sm font-medium">Chief complaint</label><input name="chief_complaint" value="{{ old('chief_complaint') }}" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-sm font-medium">Acuity</label><select name="acuity" class="mt-1 w-full rounded-md border-gray-300">@foreach($acuities as $acuity)<option value="{{ $acuity->value }}">{{ $acuity->label() }}</option>@endforeach</select></div>
                <div><label class="text-sm font-medium">Pain scale</label><input type="number" name="pain_scale" value="0" min="0" max="10" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-sm font-medium">Fall-risk score</label><input type="number" name="fall_risk_score" value="0" min="0" max="10" class="mt-1 w-full rounded-md border-gray-300"></div>
                <label class="flex items-end gap-2 text-sm"><input type="checkbox" name="pregnancy_flag" value="1"> Pregnant</label>
                <label class="flex items-end gap-2 text-sm"><input type="checkbox" name="allergies_reviewed" value="1"> Allergies reviewed</label>
                <div><label class="text-sm font-medium">BP systolic</label><input type="number" name="blood_pressure_systolic" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-sm font-medium">BP diastolic</label><input type="number" name="blood_pressure_diastolic" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-sm font-medium">Pulse</label><input type="number" name="pulse_rate" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-sm font-medium">Respiration</label><input type="number" name="respiratory_rate" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-sm font-medium">Temperature C</label><input type="number" step="0.1" name="temperature_c" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-sm font-medium">SpO2</label><input type="number" name="oxygen_saturation" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-sm font-medium">Height cm</label><input type="number" step="0.01" name="height_cm" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-sm font-medium">Weight kg</label><input type="number" step="0.01" name="weight_kg" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div class="md:col-span-3"><label class="text-sm font-medium">Notes</label><textarea name="notes" class="mt-1 w-full rounded-md border-gray-300"></textarea></div>
            </div>
            @if($errors->any())<p class="mt-3 text-sm text-red-600">{{ $errors->first() }}</p>@endif
            <button class="mt-6 rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Complete Triage</button>
        </form>
    </div></div>
</x-app-layout>
