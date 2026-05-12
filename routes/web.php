<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DailyReportController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\MapPageController;
use App\Http\Controllers\Web\PoleController;
use App\Http\Controllers\Web\PoleDetailPageController;
use App\Http\Controllers\Web\PolePageController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ReportPageController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::middleware('auth')->group(function () {
    // Page routes
    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/data-tiang', PolePageController::class)->name('poles.index');
    Route::get('/data-tiang/datatable', [PoleController::class, 'datatable'])->name('poles.datatable');
    Route::get('/data-tiang/{pole}', PoleDetailPageController::class)->name('poles.show');
    Route::get('/peta', MapPageController::class)->name('map.index');
    Route::get('/peta/markers', [MapPageController::class, 'markers'])->name('map.markers');
    Route::redirect('/aktifitas', '/aktivitas');
    Route::get('/aktivitas', ReportPageController::class)->name('reports.index');

    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Pole CRUD
    Route::post('/poles', [PoleController::class, 'store'])->middleware('role:superadmin,surveyor')->name('poles.store');
    Route::get('/poles/export', [PoleController::class, 'export'])->name('poles.export');
    Route::put('/poles/{pole}', [PoleController::class, 'update'])->middleware('role:superadmin,surveyor')->name('poles.update');
    Route::delete('/poles/bulk-delete', [PoleController::class, 'bulkDestroy'])->middleware('role:superadmin,surveyor')->name('poles.bulk-destroy');
    Route::delete('/poles/{pole}', [PoleController::class, 'destroy'])->middleware('role:superadmin,surveyor')->name('poles.destroy');
    Route::delete('/pole-photos/{photo}', [PoleController::class, 'destroyPhoto'])->middleware('role:superadmin,surveyor')->name('pole-photos.destroy');

    // Daily Reports
    Route::post('/daily-reports', [DailyReportController::class, 'store'])->middleware('role:superadmin,surveyor')->name('daily-reports.store');
    Route::delete('/daily-report-photos/{photo}', [DailyReportController::class, 'destroyPhoto'])->middleware('role:superadmin,surveyor')->name('daily-report-photos.destroy');

    // Profile
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');

    // User Management (superadmin only)
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
