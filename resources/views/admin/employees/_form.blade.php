@csrf
@if($employee->exists)
    @method('PUT')
@endif

<div x-data="{ role: '{{ old('role', $employee->exists ? $employee->user->roles->first()?->name : '') }}' }" class="space-y-6">
    <x-form-section title="Account Information" description="Create the linked login account and primary employee role.">
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $employee->email)" required />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
        @unless($employee->exists)
            <div>
                <x-input-label for="temporary_password" value="Temporary Password" />
                <x-text-input id="temporary_password" name="temporary_password" type="password" class="mt-1 block w-full" required />
                <x-input-error class="mt-2" :messages="$errors->get('temporary_password')" />
            </div>
            <div>
                <x-input-label for="temporary_password_confirmation" value="Confirm Password" />
                <x-text-input id="temporary_password_confirmation" name="temporary_password_confirmation" type="password" class="mt-1 block w-full" required />
            </div>
        @endunless
        <div>
            <x-input-label for="role" value="Role" />
            <select id="role" name="role" x-model="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="">Select role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" @selected(old('role', $employee->exists ? $employee->user->roles->first()?->name : '') === $role->name)>{{ str($role->name)->replace('-', ' ')->title() }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('role')" />
        </div>
        <div>
            <x-input-label for="account_status" value="Account Status" />
            <select id="account_status" name="account_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach($accountStatuses as $status)
                    <option value="{{ $status->value }}" @selected(old('account_status', $employee->exists ? $employee->user->status->value : 'active') === $status->value)>{{ str($status->value)->replace('_', ' ')->title() }}</option>
                @endforeach
            </select>
        </div>
    </x-form-section>

    <x-form-section title="Personal Information">
        @foreach(['first_name' => 'First Name', 'middle_name' => 'Middle Name', 'last_name' => 'Last Name', 'suffix' => 'Suffix'] as $field => $label)
            <div>
                <x-input-label :for="$field" :value="$label" />
                <x-text-input :id="$field" :name="$field" class="mt-1 block w-full" :value="old($field, $employee->{$field})" :required="in_array($field, ['first_name', 'last_name'])" />
                <x-input-error class="mt-2" :messages="$errors->get($field)" />
            </div>
        @endforeach
        <div>
            <x-input-label for="date_of_birth" value="Date of Birth" />
            <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', $employee->date_of_birth?->format('Y-m-d'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
        </div>
        <div>
            <x-input-label for="sex" value="Sex" />
            <select id="sex" name="sex" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach($sexes as $sex)
                    <option value="{{ $sex->value }}" @selected(old('sex', $employee->sex?->value) === $sex->value)>{{ $sex->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="civil_status" value="Civil Status" />
            <x-text-input id="civil_status" name="civil_status" class="mt-1 block w-full" :value="old('civil_status', $employee->civil_status)" />
        </div>
        <div>
            <x-input-label for="contact_number" value="Contact Number" />
            <x-text-input id="contact_number" name="contact_number" class="mt-1 block w-full" :value="old('contact_number', $employee->contact_number)" />
        </div>
        <div class="md:col-span-2">
            <x-input-label for="profile_photo" value="Profile Photo" />
            <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-gray-700">
            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
        </div>
        @foreach(['address_line_1' => 'Address Line 1', 'address_line_2' => 'Address Line 2', 'barangay' => 'Barangay', 'city_municipality' => 'City/Municipality', 'province' => 'Province', 'postal_code' => 'Postal Code'] as $field => $label)
            <div>
                <x-input-label :for="$field" :value="$label" />
                <x-text-input :id="$field" :name="$field" class="mt-1 block w-full" :value="old($field, $employee->{$field})" />
            </div>
        @endforeach
    </x-form-section>

    <x-form-section title="Employment Information">
        <div>
            <x-input-label for="department_id" value="Department" />
            <select id="department_id" name="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) old('department_id', $employee->department_id) === (string) $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="position" value="Position" />
            <x-text-input id="position" name="position" class="mt-1 block w-full" :value="old('position', $employee->position)" required />
        </div>
        <div>
            <x-input-label for="employment_type" value="Employment Type" />
            <select id="employment_type" name="employment_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach($employmentTypes as $type)
                    <option value="{{ $type->value }}" @selected(old('employment_type', $employee->employment_type?->value ?? 'regular') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="employment_status" value="Employment Status" />
            <select id="employment_status" name="employment_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach($employmentStatuses as $status)
                    <option value="{{ $status->value }}" @selected(old('employment_status', $employee->employment_status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="hire_date" value="Hire Date" />
            <x-text-input id="hire_date" name="hire_date" type="date" class="mt-1 block w-full" :value="old('hire_date', $employee->hire_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
        </div>
        <div class="md:col-span-2">
            <x-input-label for="work_schedule_notes" value="Work Schedule Notes" />
            <textarea id="work_schedule_notes" name="work_schedule_notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('work_schedule_notes', $employee->work_schedule_notes) }}</textarea>
        </div>
    </x-form-section>

    <x-form-section title="Professional Information" description="Doctor-specific fields are validated on the server.">
        <div>
            <x-input-label for="license_number" value="License Number" />
            <x-text-input id="license_number" name="license_number" class="mt-1 block w-full" :value="old('license_number', $employee->license_number)" />
        </div>
        <div>
            <x-input-label for="license_expiration_date" value="License Expiration" />
            <x-text-input id="license_expiration_date" name="license_expiration_date" type="date" class="mt-1 block w-full" :value="old('license_expiration_date', $employee->license_expiration_date?->format('Y-m-d'))" />
        </div>
        <div>
            <x-input-label for="specialization" value="Specialization" />
            <x-text-input id="specialization" name="specialization" class="mt-1 block w-full" :value="old('specialization', $employee->specialization)" />
            <p x-show="role === 'doctor'" class="mt-1 text-xs text-gray-500">Required for doctors.</p>
        </div>
        <div>
            <x-input-label for="consultation_fee" value="Consultation Fee" />
            <x-text-input id="consultation_fee" name="consultation_fee" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('consultation_fee', $employee->consultation_fee)" />
        </div>
        <div>
            <x-input-label for="maximum_appointments_per_day" value="Max Appointments Per Day" />
            <x-text-input id="maximum_appointments_per_day" name="maximum_appointments_per_day" type="number" min="1" max="100" class="mt-1 block w-full" :value="old('maximum_appointments_per_day', $employee->maximum_appointments_per_day)" />
        </div>
        <div>
            <x-input-label for="clinic_room" value="Clinic Room" />
            <x-text-input id="clinic_room" name="clinic_room" class="mt-1 block w-full" :value="old('clinic_room', $employee->clinic_room)" />
        </div>
    </x-form-section>

    <x-form-section title="Emergency Contact">
        @foreach(['emergency_contact_name' => 'Name', 'emergency_contact_relationship' => 'Relationship', 'emergency_contact_number' => 'Contact Number'] as $field => $label)
            <div>
                <x-input-label :for="$field" :value="$label" />
                <x-text-input :id="$field" :name="$field" class="mt-1 block w-full" :value="old($field, $employee->{$field})" />
            </div>
        @endforeach
    </x-form-section>

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.employees.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Cancel</a>
        <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Save Employee</button>
    </div>
</div>
