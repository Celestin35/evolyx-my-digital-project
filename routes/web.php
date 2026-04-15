<?php

use App\Http\Controllers\CaloriesController;
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
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::inertia('sessions', 'Sessions')->name('sessions');
    Route::inertia('nutrition', 'Nutrition')->name('nutrition');
    Route::inertia('progress', 'Progress')->name('progress');
    Route::inertia('community', 'Community')->name('community');
    Route::inertia('profile', 'Profile')->name('profile');
    Route::get('/calories/data', [CaloriesController::class, 'getUserData'])->name('calories.data');
});

Route::inertia('/calories', 'Calories')->name('calories');

require __DIR__.'/settings.php';
