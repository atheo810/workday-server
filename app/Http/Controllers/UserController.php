<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseServices;
use CaliCastle\Cuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        try {
            $users = DB::table('users')
                ->join('employees', 'users.employee_id', '=', 'employees.uuid')
                ->get([
                    'users.id', 'users.uuid', 'users.email', 'users.account_status',
                    'employees.fullname', 'employees.position', 'users.is_deleted'
                ])
                ->where('is_deleted', false)
                ->where('account_status', 'active');

            if ($users->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Data users tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $users,
                'message' => 'Data users ditemukan'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function insertUser($data)
    {
        User::create($data);

        return (string) DB::table('users')->where('email', $data['email'])->value('id');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:8',
                'employee_id' => 'required|string',
                'account_status' => 'string|in:active,inactive',
            ], [
                'email.required' => 'Email harus diisi',
                'email.email' => 'Email tidak valid',
                'email.unique' => 'Email sudah terdaftar',
                'password.required' => 'Password harus diisi',
                'password.min' => 'Password minimal 8 karakter',
                'employee_id.required' => 'Pegawai harus dipilih',
            ]);

            DB::transaction(function () use ($request) {
                $currentEmployee = DB::table('employees')
                    ->where('uuid', $request->employee_id)
                    ->first();

                $userId = $this->insertUser([
                    'uuid' => Cuid::make(),
                    'email' => strtolower($request->email),
                    'password' => Hash::make($request->password),
                    'employee_id' => $request->employee_id,
                    'account_status' => 'active'
                ]);

                FirebaseServices::insertNew('users', [
                    'user_id' => $userId,
                    'email' => strtolower($request->email),
                    'account_status' => 'active',
                    'fullname' => $currentEmployee->fullname,
                    'position' => $currentEmployee->position,
                    'avatar' => $currentEmployee->avatar
                ]);

                $currentUser = User::find($userId);
                $currentUser->assignRole($currentEmployee->position);
            });

            return response()->json([
                'code' => 201,
                'status' => 'success',
                'data' => null,
                'message' => 'User berhasil ditambahkan'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $userUuid)
    {
        $currentUser = User::where('uuid', $userUuid)->get([
            'uuid', 'email'
        ])->first();

        if (!$currentUser) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'data' => null,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => $currentUser,
            'message' => 'Data ditemukan'
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $userUuid)
    {
    }

    /*
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $userUUID)
    {
        $request->validate([
            'email' => 'required|string|email',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Email tidak valid',
        ]);

        $currentUser = User::where('uuid', $userUUID)->first();

        if (!$currentUser) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'data' => null,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $currentUser->update([
            'email' => strtolower($request->email),
        ]);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => null,
            'message' => 'User berhasil diupdate'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $userUuid)
    {
        try {
            $currentUser = User::where('uuid', $userUuid)->first();

            if (!$currentUser) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            $currentUser->update([
                'is_deleted' => true,
                'account_status' => 'inactive',
                'deleted_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => null,
                'message' => 'User berhasil dinonaktifkan'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // public function recovery(string $userUuid)
    // {
    //     User::where('uuid', $userUuid)->update([
    //         'is_deleted' => false,
    //         'account_status' => 'active',
    //     ]);

    //     return redirect()
    //         ->route('admin.manage-user')
    //         ->with('success', 'User berhasil direstore!');
    // }
}
