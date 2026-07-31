<?php

namespace App\Http\Requests\Laboratory;

use App\Enums\LaboratoryResultType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLaboratoryTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('laboratory-tests.create') || $this->user()?->can('laboratory-tests.update') || false;
    }

    public function rules(): array
    {
        $id = $this->route('test')?->id;

        return [
            'laboratory_test_category_id' => ['required', 'exists:laboratory_test_categories,id'],
            'code' => ['required', 'string', 'max:50', Rule::unique('laboratory_tests', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'result_type' => ['required', Rule::enum(LaboratoryResultType::class)],
            'default_unit' => ['nullable', 'string', 'max:50'],
            'specimen_type_id' => ['nullable', 'exists:specimen_types,id'],
            'estimated_turnaround_minutes' => ['nullable', 'integer', 'min:1'],
            'requires_fasting' => ['nullable', 'boolean'],
            'requires_verification' => ['nullable', 'boolean'],
            'is_panel' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'component_test_ids' => ['nullable', 'array'],
            'component_test_ids.*' => ['integer', 'exists:laboratory_tests,id', 'different:id'],
        ];
    }
}
