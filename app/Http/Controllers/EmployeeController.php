<?php

namespace App\Http\Controllers;

use App\Helpers\Text;
use CaliCastle\Cuid;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::pluck('name')->toArray();
        $genders = ['laki-laki', 'perempuan'];

        $employees = DB::table('employees')
            ->get([
                'id', 'uuid', 'fullname', 'gender',
                'position', 'phone_number', 'avatar',
                'address', 'joined_company_at', 'status',
                'is_deleted'
            ]);

        return view('admin.employee.index', compact('genders', 'roles', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'fullname' => 'required|string|max:70',
                'gender' => 'required|in:laki-laki,perempuan',
                'position' => 'required|in:owner,administator,head-mechanic,mechanic,cleaner,office-boy,security',
                'phone_number' => 'nullable|string',
                'address' => 'nullable|string',
                'joined_company_at' => 'required',
            ],
            [
                'phone_number.string' => 'Nomor telepon harus berupa angka',
                'position.in' => 'Posisi tidak valid',
            ]
        );

        $uuid = Cuid::make();
        $avatar = $request->file('avatar');
        $employeeCode = Text::generateIdCardNumber();

        Employee::create([
            'fullname' => $request->fullname,
            'uuid' => $uuid,
            'gender' =>  $request->gender,
            'position' => $request->position,
            'employee_code' => $employeeCode,
            'phone_number' => Text::formatPhoneNumber($request->phone_number),
            'avatar' => $request->hasFile('avatar') ? $avatar->storeAs('photos/employee', "{$employeeCode}-{$uuid}.{$avatar->extension()}") : null,
            'address' => $request->address,
            'joined_company_at' => $request->joined_company_at
        ]);

        return redirect()
            ->route('admin.manage-employee')
            ->with('success', 'Data Pegawai berhasil di tambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $employeeUuid)
    {
        // get current employee
        $currentEmployee = Employee::where('uuid', $employeeUuid)->get([
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
            'message' => 'Data ditemukan'
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $employeeUuid)
    {
        // get Role
        $roles = Role::pluck('name')->toArray();
        // gender array
        $genders = ['laki-laki', 'perempuan'];
        // get current employee
        $currentEmployee = Employee::where('uuid', $employeeUuid)->get([
            'uuid', 'fullname', 'gender', 'position', 'phone_number', 'avatar',
            'address', 'joined_company_at'
        ])->first();

        return view('admin.employee.edit', compact('currentEmployee', 'employeeUuid', 'genders', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $employeeUuid)
    {

        $validated = $request->validate(
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
        if ($validated) {
            $currentEmployee = Employee::where('uuid', $employeeUuid)->first();
            if (!$currentEmployee) {
                return back()->with('error', 'Pegawai tidak ditemukan');
            }
            $avatar = $request->file('avatar');

            $currentEmployee->update([
                'fullname' => $request->fullname,
                'gender' => $request->gender,
                'position' => $request->position,
                'phone_number' => Text::formatPhoneNumber($request->phone_number),
                'address' => $request->address,
                'joined_company_at' => $request->joined_company_at,
                'avatar' => $request->hasFile('avatar') ? $avatar->storeAs('photos/employee', "{$employeeUuid}.{$avatar->extension()}") : null,
            ]);
            if (!$currentEmployee) {
                return back()->with('error', 'Data Pegawai gagal di update');
            }
        }

        return redirect()->route('admin.manage-employee')
            ->with('success', 'Data Pegawai berhasil di Update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $employeeUuid)
    {
        Employee::where('uuid', $employeeUuid)->update([
            'is_deleted' => true,
            'status' => 'inactive'
        ]);

        return redirect()->route('admin.manage-employee')->with('success', 'Pegawai berhasil dihapus!');
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
