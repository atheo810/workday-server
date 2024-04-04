<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
