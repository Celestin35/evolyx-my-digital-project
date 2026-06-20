<?php

namespace App\Http\Requests\Nutrition;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMacrosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'protein' => ['required', 'integer', 'min:0', 'max:600'],
            'carbs' => ['required', 'integer', 'min:0', 'max:900'],
            'fats' => ['required', 'integer', 'min:0', 'max:300'],
        ];
    }
}
