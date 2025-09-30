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
    
    // Shared habits routes
    Route::get('shared-habits', [SharedHabitsController::class, 'index'])->name('shared-habits.index');
    Route::post('shared-habits/{habit}/accept', [SharedHabitsController::class, 'accept'])->name('shared-habits.accept');
    Route::post('shared-habits/{habit}/refuse', [SharedHabitsController::class, 'refuse'])->name('shared-habits.refuse');
    Route::post('shared-habits/{habit}/abandon', [SharedHabitsController::class, 'abandon'])->name('shared-habits.abandon');
});
