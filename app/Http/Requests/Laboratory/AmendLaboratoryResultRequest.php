<?php

namespace App\Http\Requests\Laboratory;

class AmendLaboratoryResultRequest extends StoreLaboratoryResultRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('laboratory-results.amend') ?? false;
    }

    public function rules(): array
    {
        return parent::rules() + ['amendment_reason' => ['required', 'string', 'min:10', 'max:1000']];
    }
}
