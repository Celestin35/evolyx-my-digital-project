<?php

namespace App\Http\Controllers;

use App\Services\DashboardDataService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardDataService $dashboardDataService): Response
    {
        return Inertia::render('Dashboard', $dashboardDataService->getForUser($request->user()));
    }
}
