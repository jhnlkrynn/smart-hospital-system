@csrf
@if($department->exists)
    @method('PUT')
@endif

<x-form-section title="Department Information">
    <div>
        <x-input-label for="code" value="Code" />
        <x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $department->code)" required />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $department->name)" required />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $department->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>
    <div>
        <x-input-label for="location" value="Location" />
        <x-text-input id="location" name="location" class="mt-1 block w-full" :value="old('location', $department->location)" />
    </div>
    <div>
        <x-input-label for="contact_number" value="Contact Number" />
        <x-text-input id="contact_number" name="contact_number" class="mt-1 block w-full" :value="old('contact_number', $department->contact_number)" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $department->email)" />
    </div>
    <div>
        <x-input-label for="department_head_employee_id" value="Department Head" />
        <select id="department_head_employee_id" name="department_head_employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">No department head</option>
            @foreach($heads as $head)
                <option value="{{ $head->id }}" @selected((string) old('department_head_employee_id', $department->department_head_employee_id) === (string) $head->id)>{{ $head->full_name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('department_head_employee_id')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $department->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
</x-form-section>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('admin.departments.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Cancel</a>
    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Save Department</button>
</div>
