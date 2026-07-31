<?php

namespace App\Http\Requests\Queue;

use Illuminate\Foundation\Http\FormRequest;

class QueueTransitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('queues.manage') || $this->user()?->can('queues.call') || $this->user()?->can('queues.complete');
    }

    public function rules(): array
    {
        return ['notes' => ['nullable', 'string', 'max:1000']];
    }
}
