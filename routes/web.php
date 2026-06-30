<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\RegistrationAccountValidationController;
use App\Http\Controllers\CaloriesController;
use App\Http\Controllers\CommunityController;
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
    Route::patch('sessions/workout-sessions/{workoutSession}', [SessionsController::class, 'updateWorkoutSession'])->name('sessions.workout-sessions.update');
    Route::delete('sessions/workout-sessions/{workoutSession}', [SessionsController::class, 'destroyWorkoutSession'])->name('sessions.workout-sessions.destroy');
    Route::post('sessions/exercises', [SessionsController::class, 'storeExercise'])->name('sessions.exercises.store');
    Route::patch('sessions/exercises/{exercise}', [SessionsController::class, 'updateExercise'])->name('sessions.exercises.update');
    Route::delete('sessions/exercises/{exercise}', [SessionsController::class, 'destroyExercise'])->name('sessions.exercises.destroy');
    Route::post('sessions/performed-sessions', [SessionsController::class, 'storePerformedSession'])->name('sessions.performed-sessions.store');
    Route::match(['post', 'patch'], 'sessions/performed-sessions/{performedSession}/complete', [SessionsController::class, 'completePerformedSession'])->name('sessions.performed-sessions.complete');
    Route::get('nutrition', [CaloriesController::class, 'show'])->name('nutrition');
    Route::patch('nutrition/macros', [CaloriesController::class, 'updateMacros'])->name('nutrition.macros.update');
    Route::get('progress', [ProgressController::class, 'show'])->name('progress');
    Route::post('progress/weight-entries', [ProgressController::class, 'storeWeightEntry'])->name('progress.weight-entries.store');
    Route::get('community', [CommunityController::class, 'index'])->name('community');
    Route::post('community/posts', [CommunityController::class, 'store'])->name('community.posts.store');
    Route::patch('community/posts/{communityPost}', [CommunityController::class, 'update'])->name('community.posts.update');
    Route::delete('community/posts/{communityPost}', [CommunityController::class, 'destroy'])->name('community.posts.destroy');
    Route::get('community/users/search', [CommunityController::class, 'searchUsers'])->name('community.users.search');
    Route::get('community/users/{user}', [CommunityController::class, 'showUser'])->name('community.users.show');
    Route::post('community/users/{user}/follow', [CommunityController::class, 'followUser'])->name('community.users.follow');
    Route::delete('community/users/{user}/follow', [CommunityController::class, 'unfollowUser'])->name('community.users.unfollow');
    Route::get('profile', [UserController::class, 'show'])->name('profile');
    Route::patch('profile/personal-info', [UserController::class, 'updatePersonalInfo'])->name('profile.personal-info.update');
    Route::patch('profile/sports', [UserController::class, 'updateSports'])->name('profile.sports.update');
    Route::patch('profile/account-info', [UserController::class, 'updateAccountInfo'])->name('profile.account-info.update');
    Route::post('goals', [GoalController::class, 'store'])->name('goals.store');
    Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('/calories/data', [CaloriesController::class, 'getUserData'])->name('calories.data');
});

require __DIR__.'/settings.php';
