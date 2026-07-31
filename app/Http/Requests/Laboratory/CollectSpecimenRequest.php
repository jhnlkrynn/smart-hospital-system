<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;

class CollectSpecimenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('laboratory-requests.collect-specimen') ?? false;
    }

    public function rules(): array
    {
        return [
            'specimen_type_id' => ['required', 'exists:specimen_types,id'],
            'item_ids' => ['nullable', 'array'],
            'item_ids.*' => ['integer', 'exists:laboratory_request_items,id'],
            'collection_notes' => ['nullable', 'string', 'max:2000'],
            'container_type' => ['nullable', 'string', 'max:100'],
            'specimen_volume' => ['nullable', 'string', 'max:50'],
            'specimen_unit' => ['nullable', 'string', 'max:30'],
        ];
    }
}
