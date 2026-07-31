<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpecimenTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('laboratory-tests.create') || $this->user()?->can('laboratory-tests.update') || false;
    }

    public function rules(): array
    {
        $id = $this->route('specimen_type')?->id ?? $this->route('specimenType')?->id;

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('specimen_types', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('specimen_types', 'name')->ignore($id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'collection_instructions' => ['nullable', 'string', 'max:5000'],
            'storage_requirements' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
