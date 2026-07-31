<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class RejectAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointments.reject') ?? false;
    }

    public function rules(): array
    {
        return ['rejection_reason' => ['required', 'string', 'max:1000']];
    }
}
