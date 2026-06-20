<?php

namespace App\Http\Controllers;

use App\Http\Requests\Goals\StoreGoalRequest;
use App\Services\GoalService;

class GoalController extends Controller
{
    public function store(StoreGoalRequest $request, GoalService $goalService)
    {
        if ($error = $goalService->create($request->user(), $request->validated())) {
            return response()->json($error, 422);
        }

        return to_route('profile')->with('success', 'Objectif enregistré avec succès.');
    }
}
