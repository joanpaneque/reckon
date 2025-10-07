<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WorkOrders\WorkOrdersController;
use App\Http\Controllers\WorkOrders\WorkOrderEntriesController;
use App\Http\Controllers\Friends\FriendsController;
use App\Http\Controllers\Habits\HabitsController;
use App\Http\Controllers\Habits\UserHabitsController;
use App\Http\Controllers\Habits\SharedHabitsController;
use App\Http\Controllers\MotivationController;

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('App\Http\Middleware\RequireAuth')->group(function () {
    // Boilerplate page
    Route::get('/', function () {
        return Inertia::render('Index');
    })->name('index');

    Route::resource('work-orders', WorkOrdersController::class);

    Route::resource('work-orders.entries', WorkOrderEntriesController::class);

    Route::resource('friends', FriendsController::class);

    Route::resource('habits', HabitsController::class);

    // User habits completion routes
    Route::post('habits/{habit}/completion', [UserHabitsController::class, 'createOrUpdate'])->name('habits.completion');
    Route::post('habits/media/{userHabit}/upload', [UserHabitsController::class, 'uploadMedia'])->name('habits.media.upload');
    Route::delete('habits/media/{userHabit}/delete', [UserHabitsController::class, 'deleteMedia'])->name('habits.media.delete');

    // Shared habits routes
    Route::get('shared-habits', [SharedHabitsController::class, 'index'])->name('shared-habits.index');
    Route::post('shared-habits/{habit}/accept', [SharedHabitsController::class, 'accept'])->name('shared-habits.accept');
    Route::post('shared-habits/{habit}/refuse', [SharedHabitsController::class, 'refuse'])->name('shared-habits.refuse');
    Route::post('shared-habits/{habit}/abandon', [SharedHabitsController::class, 'abandon'])->name('shared-habits.abandon');

    // Motivation routes
    Route::post('motivations', [MotivationController::class, 'store'])->name('motivations.store');
    Route::get('motivations/unread', [MotivationController::class, 'getUnreadMotivations'])->name('motivations.unread');
    Route::get('motivations/responses', [MotivationController::class, 'getUnreadResponses'])->name('motivations.responses');
    Route::post('motivations/{motivation}/close', [MotivationController::class, 'closeMotivation'])->name('motivations.close');
    Route::post('motivations/{motivation}/close-response', [MotivationController::class, 'closeResponse'])->name('motivations.close-response');
});
