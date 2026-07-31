<?php

namespace App\Http\Requests\Patient;

use App\Enums\PatientStatus;
use App\Enums\Sex;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('patients.create');
    }

    public function rules(): array
    {
        $withAccount = $this->boolean('create_account');

        return [
            'create_account' => ['sometimes', 'boolean'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'sex' => ['required', Rule::enum(Sex::class)],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'email' => [$withAccount ? 'required' : 'nullable', 'email', 'max:255', 'unique:patients,email', 'unique:users,email'],
            'contact_number' => [$withAccount ? 'nullable' : 'required', 'nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'city_municipality' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'blood_type' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'insurance_provider' => ['nullable', 'string', 'max:255'],
            'insurance_number' => ['nullable', 'string', 'max:255'],
            'registration_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in([PatientStatus::Active->value, PatientStatus::Inactive->value])],
            'temporary_password' => [$withAccount ? 'required' : 'nullable', 'confirmed', Password::min(10)->mixedCase()->numbers()->symbols()],
        ];
    }
}
