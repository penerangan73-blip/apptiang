<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DailyReportController;
use App\Http\Controllers\Api\PoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/me/profile', [AuthController::class, 'updateProfile']);
    Route::post('/me/password', [AuthController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/poles', [PoleController::class, 'index']);
    Route::post('/poles', [PoleController::class, 'store'])->middleware('role:superadmin,surveyor');
    Route::patch('/poles/{pole}', [PoleController::class, 'update'])->middleware('role:superadmin,surveyor,verifikator');
    Route::post('/poles/{pole}', [PoleController::class, 'update'])->middleware('role:superadmin,surveyor,verifikator');
    Route::patch('/poles/{pole}/verification', [PoleController::class, 'verify'])->middleware('role:verifikator');
    Route::delete('/poles/{pole}', [PoleController::class, 'destroy'])->middleware('role:superadmin,surveyor,verifikator');

    Route::get('/daily-reports', [DailyReportController::class, 'index']);
    Route::post('/daily-reports', [DailyReportController::class, 'store'])->middleware('role:superadmin,surveyor');

    Route::middleware('role:superadmin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::patch('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
});
