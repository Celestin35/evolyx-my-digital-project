<?php

namespace App\Http\Controllers;

use App\Services\CaloriesCalculationService;
use App\Services\MacroService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CaloriesController extends Controller
{
    public function show(Request $request, MacroService $macroService): Response
    {
        return Inertia::render('Nutrition', $macroService->nutritionDataFor($request->user()));
    }

    public function updateMacros(Request $request, MacroService $macroService): RedirectResponse
    {
        $validatedData = $request->validate([
            'protein' => ['required', 'integer', 'min:0', 'max:600'],
            'carbs' => ['required', 'integer', 'min:0', 'max:900'],
            'fats' => ['required', 'integer', 'min:0', 'max:300'],
        ]);

        if ($errors = $macroService->update($request->user(), $validatedData)) {
            return to_route('nutrition')->withErrors($errors);
        }

        return to_route('nutrition')->with(
            'success',
            'Macros mises à jour avec succès.',
        );
    }

    public function getUserData(Request $request, CaloriesCalculationService $caloriesCalculationService)
    {
        $user = $request->user()->load('latestWeightEntry');

        if (! $user->current_weight) {
            return response()->json([
                'message' => 'Aucune entrée de poids disponible pour cet utilisateur.',
            ], 422);
        }

        return response()->json([
            'user_data' => [
                'age' => $user->age,
                'weight' => $user->current_weight,
                'height' => $user->height,
                'sex' => $user->sex,
                'activity_level' => $user->activity_level,
            ],
            'calculation' => $caloriesCalculationService->calculateForUser([
                'age' => $user->age,
                'weight' => $user->current_weight,
                'height' => $user->height,
                'sex' => $user->sex,
                'activity_level' => $user->activity_level,
            ]),
        ]);
    }
}
