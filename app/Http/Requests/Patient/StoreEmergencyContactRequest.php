<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmergencyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $patient = $this->route('patient');
        return $this->user()->can('patients.manage-emergency-contacts') || $patient->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:100'],
            'contact_number' => ['required', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
