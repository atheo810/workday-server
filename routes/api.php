<?php

use App\Http\Controllers\LocationOfficeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EmployeeController;

use App\Http\Controllers\TimeOffController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\PrivateService\PrivateServiceController;
use App\Http\Controllers\UserController;
use App\Models\TimeOff;

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

Route::get('/health', function () {
    return response()->json([
        'message' => 'Uhuk, I am alive!',
        'status' => 'OK',
        'code' => 200,
    ], 200);
});

Route::get('/sv/core-user/{key}', [PrivateServiceController::class, 'getCoreUser'])->name('sv.core.users');
Route::get('/sv/users/{key}/{userId}', [PrivateServiceController::class, 'getUsers'])->name('sv.users');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware(['check.role:administrator,owner'])->group(function () {
        // ============ *** GENERAL *** ============
        Route::get('/roles', [EmployeeController::class, 'getRoles'])->name('employee.roles');
        Route::get('/not-registered-employees', [EmployeeController::class, 'getNotRegisteredEmployees'])
            ->name('employee.not-registered-employees');

        // ============ *** EMPLOYEES *** ============
        Route::apiResource('employees', EmployeeController::class)->except([
            'edit', 'create'
        ]);
        Route::post('/upload/employee', [EmployeeController::class, 'uploadFile'])->name('employee.upload-file');

        // ============ *** USERS *** ============
        Route::apiResource('/users', UserController::class)->except([
            'edit', 'create'
        ]);

        // ============ *** LOCATIONS OFFICES *** ============
        Route::apiResource('/location-office', LocationOfficeController::class)->except([
            'update'
        ]);

        // ============ *** TIMEOFF OFFICES *** ============
        Route::apiResource('timeoff', TimeOffController::class)->except([
            'create', 'edit', 'update'
        ]);
        Route::patch('/timeoff/{timeoff}', [TimeOffController::class, 'update'])->name('timeoff.update');
        Route::put('validate-status/timeoff/{timeoff}', [TimeOffController::class, 'validateStatusTimeOff'])->name('timeoff.validate-status');

        Route::post('/upload/timeoff', [TimeOffController::class, 'uploadFile'])->name('time-off.upload-file');
    });

    Route::middleware(['check.role:administrator,owner,head-machanic,mechanic,cleaner,office-boy,security'])->group(function () {
        // ============ *** EMPLOYEE PRESENCE *** ============
        Route::post('/upload/presence/employee', [PresenceController::class, 'uploadFile'])->name('employee-presence.upload-file');
        Route::get('/history-presences/{employee_id}', [PresenceController::class, 'getHistoryEmployeePresence'])->name('employee.history.presence.index');

        Route::post('/presence', [PresenceController::class, 'employeePresences'])->name('employee.presences'); // employee presence clock in dan clock out
        Route::get('/presence/check/{presence_id}', [PresenceController::class, 'getCurrentPresencesEmployee'])->name('employee.current-presences');

        // ============ *** RECAP PRESENCE *** ============
        Route::get('/recap-presence/all', [PresenceController::class, 'recapEmployeePresence'])->name('recap-presence.employee.all');
        Route::get('/recap-presence/{employee_id}', [PresenceController::class, 'recapEmployeePresenceById'])->name('recap-presence.employee');
    });
});

require __DIR__ . '/auth.php';
