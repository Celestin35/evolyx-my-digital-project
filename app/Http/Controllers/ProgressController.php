<?php

namespace App\Http\Controllers;

use App\Services\ProgressDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgressController extends Controller
{
    public function show(Request $request, ProgressDataService $progressDataService): Response
    {
        return Inertia::render('Progress', $progressDataService->getForUser($request->user()));
    }

    public function storeWeightEntry(Request $request, ProgressDataService $progressDataService): RedirectResponse
    {
        $validatedData = $request->validate([
            'weight' => ['required', 'numeric', 'min:20', 'max:600'],
            'body_fat' => ['nullable', 'numeric', 'min:2', 'max:75'],
            'entry_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);

        $progressDataService->storeWeightEntry($request->user(), $validatedData);

        return to_route('progress')->with(
            'success',
            'Entrée de poids ajoutée avec succès.',
        );
    }
}
