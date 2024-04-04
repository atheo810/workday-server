<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use CaliCastle\Cuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:administrator');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = DB::table('users')
            ->join('employees', 'users.employee_id', '=', 'employees.id')
            ->get([
                'users.id', 'users.uuid', 'users.email', 'users.account_status',
                'employees.fullname', 'employees.position', 'users.is_deleted'
            ]);

        $employeeIds = User::pluck('employee_id')->filter();
        $employees = Employee::whereNotIn('id', $employeeIds)->get(['id', 'uuid', 'fullname']);

        $roles = DB::table('roles')->get(['id', 'name']);

        return view('admin.user-management.index', compact('users', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    public function getCurrentPosition($employeeId)
    {
        $currentPosition = Employee::where('id', $employeeId)->get(['position'])->first();

        if (!$currentPosition) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'data' => null,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => $currentPosition,
            'message' => 'Data ditemukan'
        ], 200);
    }

    public function insertUser($data)
    {
        User::create($data);

        return (string) DB::table('users')->where('email', $data['email'])->value('id');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'uuid' => 'string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
            'employee_id' => 'required',
            'account_status' => 'string|in:active,inactive'
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'employee_id.required' => 'Pegawai harus dipilih',
        ]);

        if ($validated) {
            DB::transaction(function () use ($request) {
                $userId = $this->insertUser([
                    'uuid' => Cuid::make(),
                    'email' => strtolower($request->email),
                    'password' => bcrypt($request->password),
                    'employee_id' => $request->employee_id,
                    'account_status' => 'active'
                ]);

                $currentUser = User::find($userId);
                $currentUser->assignRole($request->position);
            });
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
        $currentUser = User::where('uuid', $userUuid)->get([
            'uuid', 'email'
        ])->first();

        return view('admin.user-management.edit', compact('currentUser', 'userUuid'));
    }

    /*
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $userUUID)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Email tidak valid',
        ]);

        if ($validated) {
            $currentUser = User::where('uuid', $userUUID)->first();

            if (!$currentUser) {
                return back()->with('error', 'User tidak ditemukan');
            }

            $currentUser->update([
                'email' => strtolower($request->email),
            ]);

            return redirect()
                ->route('admin.manage-user')
                ->with('success', 'User berhasil diubah!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $userUuid)
    {
        User::where('uuid', $userUuid)->update([
            'is_deleted' => true,
            'account_status' => 'inactive',
        ]);

        return redirect()
            ->route('admin.manage-user')
            ->with('success', 'User berhasil dihapus!');
    }

    public function recovery(string $userUuid)
    {
        User::where('uuid', $userUuid)->update([
            'is_deleted' => false,
            'account_status' => 'active',
        ]);

        return redirect()
            ->route('admin.manage-user')
            ->with('success', 'User berhasil direstore!');
    }
}
