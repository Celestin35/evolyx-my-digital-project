<?php

namespace App\Http\Controllers;

use App\Services\WeightEntriesService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, WeightEntriesService $weightEntriesService): Response
    {
        return Inertia::render('Dashboard', [
            'weightEntries' => $weightEntriesService->getForUser($request->user()),
        ]);
    }
}
