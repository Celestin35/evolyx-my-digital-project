<?php

namespace App\Http\Requests\Sessions;

use Illuminate\Foundation\Http\FormRequest;

class CompletePerformedSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
            'performances' => ['nullable', 'array'],
            'performances.*.exercise_id' => ['required', 'integer'],
            'performances.*.weight' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'performances.*.repetitions' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'performances.*.duration_minutes' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'performances.*.distance_meters' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'performances.*.metrics' => ['nullable', 'array'],
            'performances.*.metrics.*' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }
}
