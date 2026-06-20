<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateAccountInfoRequest;
use App\Http\Requests\Profile\UpdatePersonalInfoRequest;
use App\Http\Requests\Profile\UpdateSportsRequest;
use App\Services\UserProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function show(Request $request, UserProfileService $userProfileService): Response
    {
        return Inertia::render('Profile', $userProfileService->profileDataFor($request->user()));
    }

    public function updatePersonalInfo(
        UpdatePersonalInfoRequest $request,
        UserProfileService $userProfileService,
    ): RedirectResponse {
        $userProfileService->updatePersonalInfo($request->user(), $request->validated());

        return to_route('profile')->with(
            'success',
            'Informations personnelles mises à jour avec succès.',
        );
    }

    public function updateSports(
        UpdateSportsRequest $request,
        UserProfileService $userProfileService,
    ): RedirectResponse {
        $userProfileService->syncSports($request->user(), $request->validated('sport_ids'));

        return to_route('profile')->with(
            'success',
            'Sports pratiqués mis à jour avec succès.',
        );
    }

    public function updateAccountInfo(
        UpdateAccountInfoRequest $request,
        UserProfileService $userProfileService,
    ): RedirectResponse {
        $userProfileService->updateAccountInfo($request->user(), $request->validated());

        return to_route('profile')->with(
            'success',
            'Informations du compte mises à jour avec succès.',
        );
    }
}
