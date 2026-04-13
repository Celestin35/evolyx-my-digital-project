<?php

use App\Http\Controllers\CaloriesController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('/calories/data', [CaloriesController::class, 'getUserData'])->name('calories.data');
});

Route::inertia('/calories', 'Calories')->name('calories');

require __DIR__.'/settings.php';
