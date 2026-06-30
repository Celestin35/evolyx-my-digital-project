<?php

namespace App\Http\Requests\Progress;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeightEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'weight' => ['required', 'numeric', 'min:20', 'max:600'],
            'body_fat' => ['nullable', 'numeric', 'min:2', 'max:75'],
            'entry_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ];
    }
}
