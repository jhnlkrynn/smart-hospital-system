<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;

class RejectSpecimenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('laboratory-requests.reject-specimen') ?? false;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:10', 'max:1000']];
    }
}
