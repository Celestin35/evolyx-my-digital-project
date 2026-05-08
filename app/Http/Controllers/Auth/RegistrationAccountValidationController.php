<?php

namespace App\Http\Controllers\Auth;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationAccountValidationController extends Controller
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ], $this->profileValidationMessages());

        return response()->json(['valid' => true]);
    }
}
