<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DailyReportController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PoleController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::post('/poles', [PoleController::class, 'store'])->middleware('role:superadmin,surveyor')->name('poles.store');
    Route::get('/poles/export', [PoleController::class, 'export'])->name('poles.export');
    Route::put('/poles/{pole}', [PoleController::class, 'update'])->middleware('role:superadmin,surveyor')->name('poles.update');
    Route::delete('/poles/{pole}', [PoleController::class, 'destroy'])->middleware('role:superadmin,surveyor')->name('poles.destroy');

    Route::post('/daily-reports', [DailyReportController::class, 'store'])->middleware('role:superadmin,surveyor')->name('daily-reports.store');

    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');

    Route::middleware('role:superadmin')->group(function () {
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
