<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\RegistrationAccountValidationController;
use App\Http\Controllers\CaloriesController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return Features::enabled(Features::registration())
        ? redirect()->route('register')
        : redirect()->route('login');
})->name('home');

Route::post('register/validate-account', RegistrationAccountValidationController::class)
    ->middleware('guest')
    ->name('register.validate-account');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('sessions', [SessionsController::class, 'show'])->name('sessions');
    Route::post('sessions/workout-sessions', [SessionsController::class, 'storeWorkoutSession'])->name('sessions.workout-sessions.store');
    Route::post('sessions/exercises', [SessionsController::class, 'storeExercise'])->name('sessions.exercises.store');
    Route::post('sessions/performed-sessions', [SessionsController::class, 'storePerformedSession'])->name('sessions.performed-sessions.store');
    Route::patch('sessions/performed-sessions/{performedSession}/complete', [SessionsController::class, 'completePerformedSession'])->name('sessions.performed-sessions.complete');
    Route::get('nutrition', [CaloriesController::class, 'show'])->name('nutrition');
    Route::patch('nutrition/macros', [CaloriesController::class, 'updateMacros'])->name('nutrition.macros.update');
    Route::get('progress', [ProgressController::class, 'show'])->name('progress');
    Route::post('progress/weight-entries', [ProgressController::class, 'storeWeightEntry'])->name('progress.weight-entries.store');
    Route::inertia('community', 'Community')->name('community');
    Route::get('profile', [UserController::class, 'show'])->name('profile');
    Route::patch('profile/personal-info', [UserController::class, 'updatePersonalInfo'])->name('profile.personal-info.update');
    Route::patch('profile/account-info', [UserController::class, 'updateAccountInfo'])->name('profile.account-info.update');
    Route::post('goals', [GoalController::class, 'store'])->name('goals.store');
    Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('/calories/data', [CaloriesController::class, 'getUserData'])->name('calories.data');
});

require __DIR__.'/settings.php';
