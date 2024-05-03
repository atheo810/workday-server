<?php

namespace App\Http\Controllers\PrivateService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrivateServiceController extends Controller
{
    public function getUsers(string $key, string $userId)
    {
        try {
            if ($key != env('UNIQUE_KEY')) {
                return response()->json([
                    'code' => 401,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Unauthorized access!'
                ], 401);
            }

            $users = DB::table('users')
                ->join('employees', 'users.employee_id', '=', 'employees.id')
                ->get([
                    'users.id', 'users.uuid', 'users.email', 'users.account_status', 'users.password',
                    'employees.fullname', 'employees.position', 'users.is_deleted'
                ])
                ->where('is_deleted', false)
                ->where('account_status', 'active')
                ->where('uuid', $userId)
                ->first();

            if (!$users) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Data users not found!'
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $users,
                'message' => 'Data users found!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
                'data' => null,
                'code' => 500,
            ], 500);
        }
    }
}
