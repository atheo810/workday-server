<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthenticatedSessionController::class, 'getProfile'])
        ->name('auth.get-profile');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
