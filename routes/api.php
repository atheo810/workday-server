<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EmployeeController;

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

Route::middleware('auth:sanctum')->group(function () {
    // ============ *** GENERAL *** ============
    Route::post('/upload-file/employee', [EmployeeController::class, 'uploadFile'])->name('employee.upload-file');
    Route::get('/roles', [EmployeeController::class, 'getRoles'])->name('employee.roles');

    // ============ *** EMPLOYEES *** ============
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employee', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employee/{employee_id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::patch('/employee/{employee_id}/update', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employee/{employee_id}/delete', [EmployeeController::class, 'destroy'])->name('employees.delete');
});


require __DIR__ . '/auth.php';
