<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLaboratoryCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('laboratory-tests.create') || $this->user()?->can('laboratory-tests.update') || false;
    }

    public function rules(): array
    {
        $id = $this->route('category')?->id;

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('laboratory_test_categories', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('laboratory_test_categories', 'name')->ignore($id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
