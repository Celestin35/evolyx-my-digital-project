<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CaloriesController;
use App\Http\Controllers\GoalController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::inertia('sessions', 'Sessions')->name('sessions');
    Route::get('nutrition', [CaloriesController::class, 'show'])->name('nutrition');
    Route::inertia('progress', 'Progress')->name('progress');
    Route::inertia('community', 'Community')->name('community');
    Route::get('profile', [UserController::class, 'show'])->name('profile');
    Route::patch('profile/personal-info', [UserController::class, 'updatePersonalInfo'])->name('profile.personal-info.update');
    Route::patch('profile/account-info', [UserController::class, 'updateAccountInfo'])->name('profile.account-info.update');
    Route::post('goals', [GoalController::class, 'store'])->name('goals.store');
    Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('/calories/data', [CaloriesController::class, 'getUserData'])->name('calories.data');
});

require __DIR__.'/settings.php';
