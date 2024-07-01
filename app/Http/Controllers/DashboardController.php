<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Presence;
use App\Models\TimeOff;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function privateDashboard()
    {
        return view('dashboard');
    }

    public function publicDashboard()
    {
        return view('public-dashboard');
    }

    public function getAllInformation(Request $request)
    {
        $currentDate = $request->query('date');

        try {
            $employees = Employee::all()->where('is_deleted', false)->count();
            $presence = $currentDate ? Presence::where('presence_date', $currentDate)->count() : Presence::all()->count();
            $timeoff = $currentDate ? TimeOff::where('timeoff_created_at', $currentDate)->where('status', 'approved')->count() : TimeOff::all()->count();
            $timeoffAll = TimeOff::all()->where('status', 'approved')->count();
            $timeoffPending = TimeOff::where('status', 'pending')->count();

            $resource = [
                'employees' => $employees,
                'presences' => $presence,
                'timeoffs' => $timeoff,
                'timeoff_all' => $timeoffAll,
                'timeoffPending' => $timeoffPending,
            ];

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $resource,
                'message' => 'Success to get all information',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get all information',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
