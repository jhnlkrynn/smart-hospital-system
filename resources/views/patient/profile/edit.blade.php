<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">Edit My Patient Profile</h2></x-slot>
    <div class="py-8"><form method="POST" enctype="multipart/form-data" action="{{ route('patient.profile.update') }}" class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">@csrf @method('PUT')
        <x-form-section title="Allowed Self-Service Fields">
            @foreach(['civil_status' => 'Civil Status', 'contact_number' => 'Contact Number', 'address_line_1' => 'Address Line 1', 'address_line_2' => 'Address Line 2', 'barangay' => 'Barangay', 'city_municipality' => 'City/Municipality', 'province' => 'Province', 'postal_code' => 'Postal Code', 'insurance_provider' => 'Insurance Provider', 'insurance_number' => 'Insurance Number'] as $field => $label)
                <div><x-input-label :for="$field" :value="$label" /><x-text-input :id="$field" :name="$field" class="mt-1 block w-full" :value="old($field, $patient->{$field})" /></div>
            @endforeach
            <div class="md:col-span-2"><x-input-label for="profile_photo" value="Profile Photo" /><input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm"></div>
        </x-form-section>
        <div class="flex justify-end gap-3"><a href="{{ route('patient.profile.show') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm">Cancel</a><button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Save</button></div>
    </form></div>
</x-app-layout>
