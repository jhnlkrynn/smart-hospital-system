<?php

namespace App\Http\Requests\Laboratory;

use App\Enums\LaboratoryResultType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLaboratoryResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('laboratory-results.enter') ?? false;
    }

    public function rules(): array
    {
        $type = $this->route('item')?->result_type_snapshot;

        return [
            'numeric_value' => [$type === LaboratoryResultType::Numeric ? 'required' : 'nullable', 'numeric'],
            'text_value' => [$type === LaboratoryResultType::Text ? 'required' : 'nullable', 'string', 'max:10000'],
            'qualitative_value' => [$type === LaboratoryResultType::Qualitative ? 'required' : 'nullable', 'string', 'max:255'],
            'boolean_value' => [$type === LaboratoryResultType::Boolean ? 'required' : 'nullable', 'boolean'],
            'structured_value' => [$type === LaboratoryResultType::Structured ? 'required' : 'nullable', 'array'],
            'unit' => ['nullable', 'string', 'max:50'],
            'technical_notes' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
