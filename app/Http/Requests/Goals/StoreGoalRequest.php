<?php

namespace App\Http\Requests\Goals;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_weight' => ['required', 'numeric', 'min:20', 'max:600'],
            'weekly_weight_goal' => [
                'required',
                'numeric',
                'min:-1',
                'max:1',
                function (string $attribute, mixed $value, Closure $fail) {
                    $allowedValues = [-1.0, -0.75, -0.5, -0.25, 0.0, 0.25, 0.5, 0.75, 1.0];

                    if (! in_array((float) $value, $allowedValues, true)) {
                        $fail('Le rythme hebdomadaire doit correspondre Ã  une option disponible.');
                    }
                },
            ],
        ];
    }
}
