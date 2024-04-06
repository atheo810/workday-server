<?php

namespace App\Http\Controllers;

use App\Helpers\Text;
use CaliCastle\Cuid;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function getNotRegisteredEmployees()
    {
        try {
            $employeeIds = User::pluck('employee_id')->filter();
            $employees = Employee::whereNotIn('id', $employeeIds)->get(['id', 'uuid', 'fullname']);

            if ($employees->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => [],
                    'message' => 'Semua pegawai sudah terdaftar sebagai user'
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $employees,
                'message' => 'Pitemukan'
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

    public function getRoles()
    {
        try {
            $roles = Role::pluck('name')->toArray();

            if (empty($roles)) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Poles tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $roles,
                'message' => 'Poles berhasil didapatkan'
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

    public function index()
    {
        try {
            $employees = DB::table('employees')
                ->get([
                    'id', 'uuid', 'fullname', 'gender',
                    'position', 'phone_number', 'avatar',
                    'address', 'joined_company_at', 'status',
                    'is_deleted'
                ])
                ->where('is_deleted', false)
                ->where('status', 'active');

            if ($employees->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Pegawai tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $employees,
                'message' => 'Pegawai berhasil didapatkan',
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:jpeg,jpg,png|max:3072'
            ]);

            $file = $request->file('file');
            $fileName = Cuid::make();
            $resultFile = $file
                ? $file->storeAs('photos/employee', "{$fileName}.{$file->extension()}")
                : null;

            $baseUrl = Storage::url($resultFile);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'url' => $baseUrl
                ],
                'message' => 'File berhasil di upload'
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'fullname' => 'required|string|max:70',
                    'gender' => 'required|in:laki-laki,perempuan',
                    'position' => 'required|in:owner,administator,head-mechanic,mechanic,cleaner,office-boy,security',
                    'phone_number' => 'nullable|string',
                    'address' => 'nullable|string',
                    'avatar' => 'nullable|string',
                    'joined_company_at' => 'required',
                ],
                [
                    'phone_number.string' => 'Nomor telepon harus berupa angka',
                    'position.in' => 'Posisi tidak valid',
                ]
            );

            $uuid = Cuid::make();
            $employeeCode = Text::generateIdCardNumber();

            Employee::create([
                'fullname' => $request->fullname,
                'uuid' => $uuid,
                'gender' =>  $request->gender,
                'position' => $request->position,
                'employee_code' => $employeeCode,
                'phone_number' => Text::formatPhoneNumber($request->phone_number),
                'avatar' => $request->avatar,
                'address' => $request->address,
                'joined_company_at' => $request->joined_company_at
            ]);

            return response()->json([
                'status' => 'success',
                'code' => 201,
                'data' => null,
                'message' => 'Pegawai berhasil ditambahkan'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'data' => null,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $employee_id)
    {
        try {
            $currentEmployee = Employee::where('uuid', $employee_id)->get([
                'uuid', 'fullname', 'gender', 'position', 'phone_number', 'avatar',
                'address', 'joined_company_at'
            ])->first();

            if (!$currentEmployee) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Pegawai tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $currentEmployee,
                'message' => 'Pegawai berhasil didapatkan'
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $employeeUuid)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $employee_id)
    {
        try {
            $request->validate(
                [
                    'fullname' => 'required|string|max:70',
                    'gender' => 'required|in:laki-laki,perempuan',
                    'position' => 'required|in:owner,administrator,head-mechanic,mechanic,cleaner,office-boy,security',
                    'address' => 'nullable|string',
                    'joined_company_at' =>  'required|string',
                ],
                [
                    'phone_number.string' => 'Nomor telepon harus berupa angka',
                    'position.in' => 'Posisi tidak valid',
                ]
            );

            $currentEmployee = Employee::where('uuid', $employee_id)->first();

            if (!$currentEmployee) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Pegawai tidak ditemukan'
                ], 404);
            }

            $currentEmployee->update([
                'fullname' => $request->fullname,
                'gender' => $request->gender,
                'position' => $request->position,
                'phone_number' => Text::formatPhoneNumber($request->phone_number),
                'address' => $request->address,
                'joined_company_at' => $request->joined_company_at,
                'avatar' => $request->avatar,
            ]);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => null,
                'message' => 'Pegawai berhasil diupdate'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage()
            ], 422);
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $employeeUuid)
    {
        try {
            $employee = Employee::with('user')
                ->where('uuid', $employeeUuid)
                ->where('is_deleted', false)
                ->where('status', 'active')
                ->firstOrFail();

            if (!$employee) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Pegawai tidak ditemukan'
                ], 404);
            }

            $employee->update([
                'is_deleted' => true,
                'status' => 'inactive',
                'deleted_at' => now()
            ]);

            if ($employee->user) {
                $employee->user->update([
                    'is_deleted' => true,
                    'account_status' => 'inactive',
                    'deleted_at' => now()
                ]);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => null,
                'message' => 'Pegawai berhasil dinonaktifkan'
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

    public function recovery(string $employeeUuid)
    {
        Employee::where('uuid', $employeeUuid)->update([
            'is_deleted' => false,
            'status' => 'active'
        ]);

        return redirect()->route('admin.manage-employee')->with('success', 'Pegawai berhasil direstore!');
    }
}
