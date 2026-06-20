<?php

namespace App\Http\Controllers;

use App\Http\Requests\Progress\StoreWeightEntryRequest;
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

    public function storeWeightEntry(
        StoreWeightEntryRequest $request,
        ProgressDataService $progressDataService,
    ): RedirectResponse {
        $progressDataService->storeWeightEntry($request->user(), $request->validated());

        return to_route('progress')->with(
            'success',
            'Entrée de poids ajoutée avec succès.',
        );
    }
}
