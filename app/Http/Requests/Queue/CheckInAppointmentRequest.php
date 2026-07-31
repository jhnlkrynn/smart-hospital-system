<?php

namespace App\Http\Requests\Queue;

use Illuminate\Foundation\Http\FormRequest;

class CheckInAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointments.check-in') ?? false;
    }

    public function rules(): array
    {
        return [
            'is_emergency' => ['boolean'],
            'is_senior_citizen' => ['boolean'],
            'is_pwd' => ['boolean'],
            'is_pregnant' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
