<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WorkOrders\WorkOrdersController;
use App\Http\Controllers\WorkOrders\WorkOrderEntriesController;
use App\Http\Controllers\Friends\FriendsController;

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
});
