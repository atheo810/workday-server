<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login');

Route::middleware('auth:sanctum', 'check.role:administrator,owner')->group(function () {
    Route::get('/profile', [AuthenticatedSessionController::class, 'getProfile'])
        ->name('auth.get-profile');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // ONLY THIRD PARTY SERVICE
    Route::get('/get-user', [AuthenticatedSessionController::class, 'getUser'])
        ->name('auth.get-user');
});
