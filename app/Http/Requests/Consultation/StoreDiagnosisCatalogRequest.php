<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiagnosisCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('diagnoses.manage-catalog') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('diagnosis_catalog')?->id ?? $this->route('diagnosisCatalog')?->id;

        return [
            'code' => ['nullable', 'string', 'max:50', Rule::unique('diagnosis_catalog', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'is_patient_visible_default' => ['nullable', 'boolean'],
        ];
    }
}
