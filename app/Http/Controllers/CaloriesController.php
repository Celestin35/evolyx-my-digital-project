<?php

namespace App\Http\Controllers;

use App\Services\CaloriesCalculationService;
use Illuminate\Http\Request;

class CaloriesController extends Controller
{
    public function getUserData(Request $request, CaloriesCalculationService $caloriesCalculationService)
    {
        $user = $request->user()->load('latestWeightEntry');

        if (! $user->current_weight) {
            return response()->json([
                'message' => 'Aucune entree de poids disponible pour cet utilisateur.',
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
