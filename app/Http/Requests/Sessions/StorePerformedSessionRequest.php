<?php

namespace App\Http\Requests\Sessions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePerformedSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workout_session_id' => [
                'required',
                'integer',
                Rule::exists('workout_sessions', 'id')->where(
                    fn ($query) => $query
                        ->where('user_id', $this->user()->id)
                        ->orWhereNull('user_id'),
                ),
            ],
            'performed_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
