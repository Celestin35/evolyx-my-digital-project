<?php

namespace App\Http\Controllers;

use App\Services\GoalService;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function store(Request $request, GoalService $goalService)
    {
        $validatedData = $request->validate([
            'target_weight' => ['required', 'numeric', 'min:20', 'max:600'],
            'weekly_weight_goal' => [
                'required',
                'numeric',
                'min:-1',
                'max:1',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $allowedValues = [-1.0, -0.75, -0.5, -0.25, 0.0, 0.25, 0.5, 0.75, 1.0];

                    if (! in_array((float) $value, $allowedValues, true)) {
                        $fail('Le rythme hebdomadaire doit correspondre à une option disponible.');
                    }
                },
            ],
        ]);

        if ($error = $goalService->create($request->user(), $validatedData)) {
            return response()->json($error, 422);
        }

        return to_route('profile')->with('success', 'Objectif enregistré avec succès.');
    }
}
