<?php

namespace App\Http\Requests\Laboratory;

use App\Enums\LaboratoryPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLaboratoryRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('laboratory-requests.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'laboratory_test_ids' => ['required', 'array', 'min:1'],
            'laboratory_test_ids.*' => ['integer', 'distinct', 'exists:laboratory_tests,id'],
            'priority' => ['required', Rule::enum(LaboratoryPriority::class)],
            'clinical_information' => ['nullable', 'string', 'max:5000'],
            'provisional_diagnosis' => ['nullable', 'string', 'max:5000'],
            'special_instructions' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
