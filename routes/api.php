<?php

use App\Http\Controllers\LocationOfficeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {

    Route::middleware(['check.role:administrator,owner'])->group(function () {
        // ============ *** GENERAL *** ============
        Route::post('/upload-file/employee', [EmployeeController::class, 'uploadFile'])->name('employee.upload-file');
        Route::get('/roles', [EmployeeController::class, 'getRoles'])->name('employee.roles');
        Route::get('/not-registered-employees', [EmployeeController::class, 'getNotRegisteredEmployees'])
            ->name('employee.not-registered-employees');

        // ============ *** EMPLOYEES *** ============
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employee', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employee/{employee_id}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::patch('/employee/{employee_id}/update', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employee/{employee_id}/delete', [EmployeeController::class, 'destroy'])->name('employees.delete');

        // ============ *** USERS *** ============
        Route::apiResource('/users', UserController::class)->except([
            'edit', 'create'
        ]);

        // ============ *** LOCATIONS OFFICES *** ============
        Route::apiResource('/location-office', LocationOfficeController::class)->except([
            'update'
        ]);
    });
});

require __DIR__ . '/auth.php';
