<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistrationAccountValidationRequest;
use Illuminate\Http\JsonResponse;

class RegistrationAccountValidationController extends Controller
{
    public function __invoke(RegistrationAccountValidationRequest $request): JsonResponse
    {
        return response()->json(['valid' => true]);
    }
}
