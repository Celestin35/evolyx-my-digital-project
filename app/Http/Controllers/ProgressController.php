<?php

namespace App\Http\Controllers;

use App\Models\Performance;
use App\Services\WeightEntriesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgressController extends Controller
{
    public function show(Request $request, WeightEntriesService $weightEntriesService): Response
    {
        $performances = Performance::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'exercise:id,name,sport_id',
                'exercise.sport:id,name',
            ])
            ->latest('performed_at')
            ->get([
                'id',
                'performed_at',
                'weight',
                'repetitions',
                'duration_minutes',
                'distance_meters',
                'exercise_id',
            ]);

        return Inertia::render('Progress', [
            'weightEntries' => $weightEntriesService->getForUser($request->user()),
            'performances' => $performances->map(fn ($performance) => [
                'id' => $performance->id,
                'performed_at' => $performance->performed_at?->toISOString(),
                'weight' => $performance->weight !== null ? (float) $performance->weight : null,
                'repetitions' => $performance->repetitions,
                'duration_minutes' => $performance->duration_minutes !== null
                    ? (float) $performance->duration_minutes
                    : null,
                'distance_meters' => $performance->distance_meters !== null
                    ? (float) $performance->distance_meters
                    : null,
                'exercise_id' => $performance->exercise_id,
                'exercise_name' => $performance->exercise?->name ?? 'Exercice',
                'sport_id' => $performance->exercise?->sport_id,
                'sport_name' => $performance->exercise?->sport?->name,
            ]),
        ]);
    }

    public function storeWeightEntry(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'weight' => ['required', 'numeric', 'min:20', 'max:500'],
            'body_fat' => ['nullable', 'numeric', 'min:2', 'max:75'],
        ]);

        $request->user()->weightEntries()->create([
            'weight' => $validatedData['weight'],
            'body_fat' => $validatedData['body_fat'] ?? null,
        ]);

        return to_route('progress')->with(
            'success',
            'Entree de poids ajoutee avec succes.',
        );
    }
}
