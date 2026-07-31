@csrf
<div class="grid gap-4 md:grid-cols-2">
    <div><label class="text-sm font-medium">Patient</label><select name="patient_id" class="mt-1 w-full rounded-md border-gray-300">@foreach($patients as $patient)<option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>{{ $patient->patient_number }} - {{ $patient->full_name }}</option>@endforeach</select></div>
    <div><label class="text-sm font-medium">Doctor</label><select name="doctor_employee_id" class="mt-1 w-full rounded-md border-gray-300">@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected(old('doctor_employee_id') == $doctor->id)>{{ $doctor->full_name }} - {{ $doctor->department?->name }}</option>@endforeach</select></div>
    <div><label class="text-sm font-medium">Type</label><select name="appointment_type_id" class="mt-1 w-full rounded-md border-gray-300">@foreach($types as $type)<option value="{{ $type->id }}" @selected(old('appointment_type_id') == $type->id)>{{ $type->code }} - {{ $type->name }}</option>@endforeach</select></div>
    <div><label class="text-sm font-medium">Date</label><input type="date" name="appointment_date" value="{{ old('appointment_date', now('Asia/Manila')->addDay()->toDateString()) }}" class="mt-1 w-full rounded-md border-gray-300"></div>
    <div><label class="text-sm font-medium">Start time</label><input type="time" name="start_time" value="{{ old('start_time') }}" class="mt-1 w-full rounded-md border-gray-300"></div>
    <div><label class="text-sm font-medium">Duration override</label><input type="number" name="duration_minutes" value="{{ old('duration_minutes') }}" placeholder="Uses type default" class="mt-1 w-full rounded-md border-gray-300"></div>
    <div class="md:col-span-2"><label class="text-sm font-medium">Reason for visit</label><input name="reason_for_visit" value="{{ old('reason_for_visit') }}" class="mt-1 w-full rounded-md border-gray-300"></div>
    <div><label class="text-sm font-medium">Patient notes</label><textarea name="patient_notes" class="mt-1 w-full rounded-md border-gray-300">{{ old('patient_notes') }}</textarea></div>
    <div><label class="text-sm font-medium">Staff notes</label><textarea name="staff_notes" class="mt-1 w-full rounded-md border-gray-300">{{ old('staff_notes') }}</textarea></div>
</div>
@if($errors->any())<div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
<div class="mt-6"><button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Book Appointment</button></div>
