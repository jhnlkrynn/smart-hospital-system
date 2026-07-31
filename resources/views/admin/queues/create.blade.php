<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">Add Walk-in Patient</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-4xl px-4">
        <form method="POST" action="{{ route('admin.queues.store') }}" class="rounded-lg border bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div><label class="text-sm font-medium">Patient</label><select name="patient_id" class="mt-1 w-full rounded-md border-gray-300">@foreach($patients as $patient)<option value="{{ $patient->id }}">{{ $patient->patient_number }} - {{ $patient->full_name }}</option>@endforeach</select></div>
                <div><label class="text-sm font-medium">Department</label><select name="department_id" class="mt-1 w-full rounded-md border-gray-300">@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->code }} - {{ $department->name }}</option>@endforeach</select></div>
                <div><label class="text-sm font-medium">Doctor</label><select name="doctor_employee_id" class="mt-1 w-full rounded-md border-gray-300"><option value="">Unassigned</option>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>@endforeach</select></div>
                <div class="grid grid-cols-2 gap-2 pt-7 text-sm">
                    <label><input type="checkbox" name="is_emergency" value="1"> Emergency</label>
                    <label><input type="checkbox" name="is_senior_citizen" value="1"> Senior</label>
                    <label><input type="checkbox" name="is_pwd" value="1"> PWD</label>
                    <label><input type="checkbox" name="is_pregnant" value="1"> Pregnant</label>
                </div>
                <div class="md:col-span-2"><label class="text-sm font-medium">Notes</label><textarea name="notes" class="mt-1 w-full rounded-md border-gray-300">{{ old('notes') }}</textarea></div>
            </div>
            @if($errors->any())<p class="mt-3 text-sm text-red-600">{{ $errors->first() }}</p>@endif
            <button class="mt-6 rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Add to Queue</button>
        </form>
    </div></div>
</x-app-layout>
