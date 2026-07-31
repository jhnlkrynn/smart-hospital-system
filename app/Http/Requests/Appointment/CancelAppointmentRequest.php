<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class CancelAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointments.cancel') ?? false;
    }

    public function rules(): array
    {
        return ['cancellation_reason' => ['required', 'string', 'max:1000']];
    }
}
