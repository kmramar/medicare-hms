<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDoctorController;
use App\Http\Controllers\Admin\AdminPatientController;
use App\Http\Controllers\Admin\AdminAppointmentController;
use App\Http\Controllers\Admin\AdminBedController;
use App\Http\Controllers\Admin\AdminBillingController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    Route::get('/doctors', [AdminDoctorController::class, 'index']);
    Route::post('/doctors', [AdminDoctorController::class, 'store']);
    Route::put('/doctors/{id}', [AdminDoctorController::class, 'update']);
    Route::delete('/doctors/{id}', [AdminDoctorController::class, 'destroy']);

    Route::get('/patients', [AdminPatientController::class, 'index']);
    Route::get('/patients/{id}', [AdminPatientController::class, 'show']);

    Route::get('/appointments', [AdminAppointmentController::class, 'index']);
    Route::put('/appointments/{id}', [AdminAppointmentController::class, 'update']);

    Route::get('/beds', [AdminBedController::class, 'index']);
    Route::get('/beds/status', [AdminBedController::class, 'status']);
    Route::post('/beds', [AdminBedController::class, 'store']);
    Route::put('/beds/{id}', [AdminBedController::class, 'update']);
    Route::delete('/beds/{id}', [AdminBedController::class, 'destroy']);

    Route::get('/billings', [AdminBillingController::class, 'index']);
});

Route::prefix('doctor')->middleware(['auth:sanctum', 'doctor'])->group(function () {
    // Doctor routes will go here
});

Route::prefix('patient')->middleware(['auth:sanctum', 'patient'])->group(function () {
    // Patient routes will go here
});
