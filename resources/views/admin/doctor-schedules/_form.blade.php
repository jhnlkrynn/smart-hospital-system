@csrf
<div class="grid gap-4 md:grid-cols-2">
    <div><label class="text-sm font-medium">Doctor</label><select name="doctor_employee_id" class="mt-1 w-full rounded-md border-gray-300">@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected(old('doctor_employee_id', $schedule->doctor_employee_id) == $doctor->id)>{{ $doctor->full_name }} - {{ $doctor->department?->code }}</option>@endforeach</select></div>
    <div><label class="text-sm font-medium">Day</label><select name="day_of_week" class="mt-1 w-full rounded-md border-gray-300">@foreach($dayOptions as $day)<option value="{{ $day->value }}" @selected(old('day_of_week', $schedule->day_of_week?->value) === $day->value)>{{ $day->label() }}</option>@endforeach</select></div>
    <div><label class="text-sm font-medium">Start</label><input type="time" name="start_time" value="{{ old('start_time', substr((string) $schedule->start_time, 0, 5)) }}" class="mt-1 w-full rounded-md border-gray-300"></div>
    <div><label class="text-sm font-medium">End</label><input type="time" name="end_time" value="{{ old('end_time', substr((string) $schedule->end_time, 0, 5)) }}" class="mt-1 w-full rounded-md border-gray-300"></div>
    <div><label class="text-sm font-medium">Break start</label><input type="time" name="break_start_time" value="{{ old('break_start_time', substr((string) $schedule->break_start_time, 0, 5)) }}" class="mt-1 w-full rounded-md border-gray-300"></div>
    <div><label class="text-sm font-medium">Break end</label><input type="time" name="break_end_time" value="{{ old('break_end_time', substr((string) $schedule->break_end_time, 0, 5)) }}" class="mt-1 w-full rounded-md border-gray-300"></div>
    <div><label class="text-sm font-medium">Slot minutes</label><input type="number" name="slot_duration_minutes" value="{{ old('slot_duration_minutes', $schedule->slot_duration_minutes ?? 30) }}" class="mt-1 w-full rounded-md border-gray-300"></div>
    <div><label class="text-sm font-medium">Daily maximum</label><input type="number" name="maximum_appointments" value="{{ old('maximum_appointments', $schedule->maximum_appointments ?? 16) }}" class="mt-1 w-full rounded-md border-gray-300"></div>
    <div><label class="text-sm font-medium">Effective from</label><input type="date" name="effective_from" value="{{ old('effective_from', $schedule->effective_from?->toDateString()) }}" class="mt-1 w-full rounded-md border-gray-300"></div>
    <div><label class="text-sm font-medium">Effective until</label><input type="date" name="effective_until" value="{{ old('effective_until', $schedule->effective_until?->toDateString()) }}" class="mt-1 w-full rounded-md border-gray-300"></div>
    <div><label class="text-sm font-medium">Clinic room</label><input name="clinic_room" value="{{ old('clinic_room', $schedule->clinic_room) }}" class="mt-1 w-full rounded-md border-gray-300"></div>
    <label class="flex items-end gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $schedule->is_active ?? true))> Active</label>
</div>
@if($errors->any())<div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
<div class="mt-6"><button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Save</button></div>
