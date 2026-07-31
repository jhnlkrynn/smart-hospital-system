<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointments.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'staff_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
