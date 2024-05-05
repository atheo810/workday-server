<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function store(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Kredensial tidak sesuai atau akun Anda telah dinonaktifkan. Silahkan coba lagi'
                ], Response::HTTP_UNAUTHORIZED);
            }

            if (!auth()->user()->hasRole('administrator|owner')) {
                return response()->json([
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses resource ini.'
                ], Response::HTTP_FORBIDDEN);
            }

            $user = User::where('email', $credentials['email'])->first();

            $user->tokens()->delete();
            $generateToken = $user->createToken(
                name: $user->value('uuid'),
                expiresAt: now()->addDay(1)
            );

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Login successful. Welcome back!',
                'data' => [
                    'id' => $user->value('uuid'),
                    'employee_id' => $user->value('employee_id'),
                    'token' => $generateToken->plainTextToken,
                    'role' => $user->getRoleNames()->first(),
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Something went wrong, please try again later',
                'data' => null
            ], 500);
        }
    }

    public function getProfile(Request $request)
    {
        try {
            $currentEmployeeId = $request->user()->employee_id;

            $userAccount = $request->user();
            $currentEmail = $userAccount->email;
            $currentAccountStatus = $userAccount->account_status;

            $totalPresences = DB::table('employees')
                ->join('presences', 'employees.uuid', '=', 'presences.employee_id')
                ->where('employees.uuid', $currentEmployeeId)
                ->count();

            $totalTimeOff = DB::table('employees')
                ->join('time_offs', 'employees.uuid', '=', 'time_offs.employee_id')
                ->where('time_offs.employee_id', $currentEmployeeId)
                ->where('time_offs.status', 'approved')
                ->count();

            $employee = DB::table('employees')
                ->get([
                    'uuid', 'id', 'fullname', 'position', 'phone_number', 'avatar',
                    'address', 'joined_company_at', 'created_at'
                ])
                ->where('uuid', $currentEmployeeId)
                ->first();

            $employeeData = array_merge([
                'email' => $currentEmail,
                'account_status' => $currentAccountStatus,
                'total_presences' => $totalPresences,
                'total_timeoffs' => $totalTimeOff,
            ], (array) $employee);

            if (!$employee) {
                return response()->json([
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Profile user not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Profile user found successfully',
                'data' => $employeeData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Something went wrong, please try again later',
                'data' => null
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Logout successful. Goodbye!',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Something went wrong, please try again later',
                'data' => null
            ], 500);
        }
    }
}
