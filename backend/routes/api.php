<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Admin routes will go here
});

Route::prefix('doctor')->middleware(['auth:sanctum', 'doctor'])->group(function () {
    // Doctor routes will go here
});

Route::prefix('patient')->middleware(['auth:sanctum', 'patient'])->group(function () {
    // Patient routes will go here
});
