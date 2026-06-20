<?php

namespace App\Http\Requests\Sessions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userSportIds = $this->user()->sports()->pluck('sports.id');

        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sport_id' => ['required', 'integer', Rule::exists('sports', 'id')->where(
                fn ($query) => $query->whereIn('id', $userSportIds),
            )],
            'exercise_category_id' => ['required', 'integer', Rule::exists('exercise_categories', 'id')],
        ];
    }
}
