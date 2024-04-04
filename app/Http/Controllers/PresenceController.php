<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Presence;
use CaliCastle\Cuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:administrator|owner|head-mechanic|mechanic|cleaner|office-boy|security');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = DB::table('employees')
            ->get([
                'fullname', 'gender', 'position', 'avatar', 'status',
                'is_deleted', 'uuid'
            ]);

        return view('public.presence.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    public function checkAlreadyAbsence(string $employeeId)
    {
        $employeeIds = Employee::where('uuid', $employeeId)->first()->id;

        $presenceIn = DB::table('presences')
            ->where('employee_id', $employeeIds)
            ->whereDate('clock_in', now())
            ->get(['uuid', 'clock_in'])
            ->first();

        $presenceOut = DB::table('presences')
            ->where('employee_id', $employeeIds)
            ->whereDate('clock_out', now())
            ->get(['uuid', 'clock_out'])
            ->first();

        if ($presenceIn || $presenceOut) {
            return response()->json([
                'status' => 'success',
                'message' => 'Employee already presence today',
                'data' => [
                    'presence_in' => $presenceIn,
                    'presence_out' => $presenceOut
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Employee not presence today',
            'data' => [
                'presence_in' => null,
                'presence_out' => null
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $employeeId)
    {
        $employee = DB::table('employees')
            ->get([
                'fullname', 'position', 'avatar', 'status',
                'is_deleted', 'uuid'
            ])
            ->where('uuid', $employeeId)
            ->first();

        $presences = DB::table('presences')
            ->get([
                'uuid', 'clock_in', 'clock_out',
                'latitude_in', 'longitude_in', 'latitude_out', 'longitude_out',
                'distance_in', 'distance_out', 'presence_by',
                'photo_in', 'photo_out', 'device'
            ]);

        return view('public.presence.detail-presence', compact('presences', 'employee', 'employeeId'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Presence $presence)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Presence $presence)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Presence $presence)
    {
        //
    }
}
