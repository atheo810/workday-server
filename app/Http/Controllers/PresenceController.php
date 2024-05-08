<?php

namespace App\Http\Controllers;

use App\Models\LocationOffice;
use App\Models\Presence;
use Barryvdh\DomPDF\Facade\Pdf;
use CaliCastle\Cuid;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PresenceController extends Controller
{
    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:jpeg,jpg,png|max:3072'
            ]);

            $file = $request->file('file');
            $fileName = Cuid::make();
            $resultFile = $file
                ? $file->storeAs('photos/presence', "{$fileName}.{$file->extension()}")
                : null;

            $baseUrl = Storage::url($resultFile);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'url' => $baseUrl
                ],
                'message' => 'Foto presensi berhasil diunggah'
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

    public function getHistoryEmployeePresence(string $employee_id)
    {
        try {
            $presences = DB::table('presences')
                ->get()
                ->where('employee_id', $employee_id);

            if ($presences->isEmpty()) {
                return response()->json([
                    'code' => '404',
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Employee presence not found',
                ], 404);
            }

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'data' => $presences,
                'message' => 'Employee presence found',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'data' => null,
                'message' => 'Failed to get employee presence',
            ], 500);
        }
    }

    public function calculateDistance($officeLatitude, $officeLongitude, $currentLatitude, $currentLongitude)
    {
        $earthRadius = 6371000; // Radius of Earth in meters
        $latFrom = deg2rad($officeLatitude);
        $lonFrom = deg2rad($officeLongitude);
        $latTo = deg2rad($currentLatitude);
        $lonTo = deg2rad($currentLongitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius);
    }

    public function getCurrentPresencesEmployee(string $employeeId)
    {
        try {
            $presenceDate = date('Y-m-d');
            $presenceUser = Presence::where('employee_id', $employeeId)
                ->where('presence_date', $presenceDate);

            $findPresence = $presenceUser->first();
            if (!$findPresence) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Presensi pegawai tidak ditemukan',
                ], 404);
            }

            // $currentPresence = $presenceUser->count();

            $isAlreadyClockIn = $findPresence->clock_in
                && $findPresence->latitude_in
                && $findPresence->longitude_in
                ? true
                : false;

            $isAlreadyClockOut = $findPresence->clock_out
                && $findPresence->latitude_out
                && $findPresence->longitude_out
                ? true
                : false;

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'presence_in' => $isAlreadyClockIn,
                    'presence_out' => $isAlreadyClockOut,
                ],
                'message' => "Presensi pegawai ditemukan"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function employeePresences(Request $request)
    {
        try {
            $employeeIdQuery = $request->query('empl_id');

            $request->validate([
                'clock_in' => 'string',
                'clok_out' => 'string',
                'ip_address' => 'string',
                'device' => 'string|in:web,android,ios',
                'latitude_in' => 'string',
                'longitude_in' => 'string',
                'latitude_out' => 'string',
                'longitude_out' => 'string',
                'employee_id' => 'string',
                'photo_in' => 'string',
                'photo_out' => 'string',
            ]);

            if (!$employeeIdQuery) {
                return response()->json([
                    'code' => 422,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Query parameter emtime_formatpl_id dibutuhkan!',
                ], 422);
            }

            $currentLocationOffice = LocationOffice::find(1)
                ->get(['latitude', 'longitude'])
                ->first();

            if (!$currentLocationOffice) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Office location not found',
                ], 404);
            }

            $presenceDate = date('Y-m-d');
            $createdBy = auth()->user()->id;
            $uuid = Cuid::make();

            $checkIsAlreadyCheckIn = Presence::where('employee_id', $employeeIdQuery)
                ->where('presence_date', $presenceDate)
                ->count();

            if ($checkIsAlreadyCheckIn > 0) {
                $distanceOut = $this->calculateDistance(
                    $request->latitude_out,
                    $request->longitude_out,
                    $currentLocationOffice->latitude,
                    $currentLocationOffice->longitude
                );
                if ($distanceOut > 100) {
                    return response()->json([
                        'code' => 422,
                        'status' => 'error',
                        'data' => null,
                        'message' => 'Lokasi kamu diluar jangkauan 100 meter',
                    ], 422);
                }

                Presence::where('employee_id', $employeeIdQuery)
                    ->where('presence_date', $presenceDate)
                    ->update([
                        'clock_out' => $request->clock_out,
                        'latitude_out' => $request->latitude_out,
                        'longitude_out' => $request->longitude_out,
                        'distance_out' => $distanceOut,
                        'photo_out' => $request->photo_out,
                    ]);

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'data' => null,
                    'message' => 'Presensi pegawai keluar berhasil disimpan',
                ], 200);
            } else {
                $distanceIn = $this->calculateDistance(
                    $request->latitude_in,
                    $request->longitude_in,
                    $currentLocationOffice->latitude,
                    $currentLocationOffice->longitude
                );

                if ($distanceIn > 100) {
                    return response()->json([
                        'code' => 422,
                        'status' => 'error',
                        'data' => null,
                        'message' => 'Lokasi kamu diluar jangkauan 100 meter',
                    ], 422);
                }

                Presence::create([
                    'uuid' => $uuid,
                    'employee_id' => $employeeIdQuery,
                    'clock_in' => $request->clock_in,
                    'ip_address' => $request->ip_address,
                    'device' => $request->device,
                    'latitude_in' => $request->latitude_in,
                    'longitude_in' => $request->longitude_in,
                    'distance_in' => $distanceIn,
                    'photo_in' => $request->photo_in,
                    'presence_date' => $presenceDate,
                ]);

                return response()->json([
                    'code' => 201,
                    'status' => 'success',
                    'data' => null,
                    'message' => 'Presensi pegawai masuk berhasil disimpan',
                ], 201);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function recapEmployeePresence(Request $request)
    {
        $monthTemplate = [
            "", "Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        try {
            $request->validate([
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d',
            ]);

            $parseStartDate = Carbon::parse($request->start_date);
            $parseEndDate = Carbon::parse($request->end_date);

            $startMonth = $parseStartDate->month;
            $endMonth = $parseEndDate->month;

            $startYear = $parseStartDate->year;
            $endYear = $parseEndDate->year;

            // store all data in months
            $months = [];
            // same year logic
            if ($startYear == $endYear) {
                for ($month = $startMonth; $month <= $endMonth; $month++) {
                    $historyPresenceByMonth = DB::table('presences')
                        ->join('employees', 'presences.employee_id', '=', 'employees.uuid')
                        // ->join('users', 'presences.presence_by', '=', 'users.id')
                        ->whereRaw('MONTH(presences.presence_date)="' . $month . '"')
                        ->orderBy('employees.id', 'asc')
                        ->select('presences.id', 'presences.employee_id', 'presences.uuid', 'presences.clock_in', 'presences.clock_out',  'presences.presence_date', 'employees.uuid as employee_id')
                        ->get()
                        ->groupBy('employee_id');


                    // foreach timeoff
                    foreach ($historyPresenceByMonth as $employeeId => $presences) {
                        $timeOffs = DB::table('time_offs')
                            ->where('time_offs.employee_id', $presences[0]->employee_id)
                            ->whereRaw('YEAR(time_offs.start_date)="' . $startYear . '"')
                            ->whereRaw('MONTH(time_offs.start_date)="' . $month . '"')
                            ->join('users', 'time_offs.accepted_by', '=', 'users.uuid')
                            ->select('time_offs.id', 'time_offs.uuid', 'users.email as accepted_by', 'time_offs.type', 'time_offs.start_date', 'time_offs.end_date', 'time_offs.status', 'time_offs.description', 'time_offs.document', 'time_offs.status', 'time_offs.total_days')
                            ->get();

                        $employeeName = DB::table('employees')
                            ->where('uuid', $presences[0]->employee_id)
                            ->select('fullname')->get();

                        $months[] = [
                            'employee_id' => $employeeId,
                            'employee_name' => $employeeName[0]->fullname,
                            'month' => $monthTemplate[$month],
                            'year' => $startYear,
                            'presences' => $presences,
                            'time_offs' => $timeOffs,
                        ];
                    }
                }
            } else {
                for ($year = $startYear; $year <= $endYear; $year++) {
                    $start = $year == $startYear ? $startMonth : 1;
                    $end = $year == $endYear ? $endMonth : 12;

                    for ($month = $start; $month <= $end; $month++) {
                        $historyPresenceByMonth = DB::table('presences')
                            ->whereRaw('YEAR(presences.presence_date)="' . $year . '"')
                            ->whereRaw('MONTH(presences.presence_date)="' . $month . '"')
                            ->join('employees', 'presences.employee_id', '=', 'employees.uuid')
                            // ->join('users', 'presences.presence_by', '=', 'users.id')
                            ->orderBy('employees.id', 'asc')
                            ->select('presences.id', 'presences.employee_id', 'presences.uuid', 'presences.clock_in', 'presences.clock_out',  'presences.presence_date', 'employees.uuid as employee_id')
                            ->get()
                            ->groupBy('employee_id');

                        foreach ($historyPresenceByMonth as $employeeId => $presences) {
                            $timeOffs = DB::table('time_offs')
                                ->where('time_offs.employee_id', $presences[0]->employee_id)
                                ->whereRaw('YEAR(time_offs.start_date)="' . $year . '"')
                                ->whereRaw('MONTH(time_offs.start_date)="' . $month . '"')
                                ->join('users', 'time_offs.accepted_by', '=', 'users.uuid')
                                ->select('time_offs.id', 'time_offs.uuid', 'users.email as accepted_by', 'time_offs.type', 'time_offs.start_date', 'time_offs.end_date', 'time_offs.status', 'time_offs.description', 'time_offs.document', 'time_offs.status', 'time_offs.total_days')
                                ->get();

                            $employeeName = DB::table('employees')
                                ->where('uuid', $presences[0]->employee_id)
                                ->select('fullname')->get();

                            $months[] = [
                                'employee_id' => $employeeId,
                                'employee_name' => $employeeName[0]->fullname,
                                'month' => $monthTemplate[$month],
                                'year' => $year,
                                'presences' => $presences,
                                'time_offs' => $timeOffs,
                            ];
                        }
                    }
                }
            }

            $pdf = Pdf::loadView('presenceAll', ['months' => $months])->setPaper('a4', 'portrait');
            $pdf->download()->getOriginalContent();

            $dateTimeNow = new DateTime();
            $dateNow = $dateTimeNow->format('Y-m-d_H-i-s');

            $pdfpath = 'presenceById_' . $dateNow . '_all_employee' . '.pdf';
            Storage::disk('public')->put($pdfpath, $pdf->output());

            $pdfFullPath = asset('storage/presence-recap/' . $pdfpath);

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'data' => [
                    'url' => $pdfFullPath
                ],
                'message' => 'Data presensi pegawai berhasil ditemukan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'data' => [
                    'error' => $e->getMessage(),
                ],
                'message' => 'tidak ada data sama sekali pada database',
            ], 404);
        }
    }

    public function recapEmployeePresenceById(Request $request, string $employeeId)
    {
        $monthTemplate = [
            "", "Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        try {
            $request->validate([
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d',
            ]);

            $parseStartDate = Carbon::parse($request->start_date);
            $parseEndDate = Carbon::parse($request->end_date);

            $startMonth = $parseStartDate->month;
            $endMonth = $parseEndDate->month;

            $startYear = $parseStartDate->year;
            $endYear = $parseEndDate->year;

            $months = [];
            $name = DB::table('employees')
                ->where('uuid', $employeeId)
                ->select('fullname')
                ->first();
            if ($startYear == $endYear) {
                for ($month = $startMonth; $month <= $endMonth; $month++) {
                    $historyPresenceByMonth = DB::table('presences')
                        ->join('employees', 'presences.employee_id', '=', 'employees.uuid')
                        ->where('employees.uuid', $employeeId)
                        ->whereRaw('MONTH(presences.presence_date)="' . $month . '"')
                        ->orderBy('presences.presence_date', 'asc')
                        ->select('presences.id', 'employees.uuid', 'employees.id as employee_id', 'presences.clock_in', 'presences.clock_out',  'presences.presence_date')
                        ->get()
                        ->groupBy('precense_date');


                    foreach ($historyPresenceByMonth as $history => $presences) {

                        $timeOffs = DB::table('time_offs')
                            ->join('users', 'time_offs.accepted_by', '=', 'users.uuid')
                            ->join('employees', 'time_offs.employee_id', '=', 'employees.uuid')
                            ->where('employees.uuid', $request->employee_id)
                            ->whereRaw('YEAR(time_offs.start_date)="' . $startYear . '"')
                            ->whereRaw('MONTH(time_offs.start_date)="' . $month . '"')
                            ->select('time_offs.id', 'time_offs.uuid', 'users.email as accepted_by', 'time_offs.type', 'time_offs.start_date', 'time_offs.end_date', 'time_offs.status', 'time_offs.description', 'time_offs.document', 'time_offs.status', 'time_offs.total_days')
                            ->get();

                        $months[] = [
                            'year' => $startYear,
                            'month' => $monthTemplate[$month],
                            'presences' => $presences,
                            'time_offs' => $timeOffs,
                        ];
                    }
                }
            } else {
                for ($year = $startYear; $year <= $endYear; $year++) {
                    $start = $year == $startYear ? $startMonth : 1;
                    $end = $year == $endYear ? $endMonth : 12;

                    for ($month = $start; $month <= $end; $month++) {
                        $historyPresenceByMonth = DB::table('presences')
                            ->join('employees', 'presences.employee_id', '=', 'employees.uuid')
                            ->where('employees.uuid', $employeeId)
                            ->whereRaw('Year(presences.presence_date)="' . $year . '"')
                            ->whereRaw('MONTH(presences.presence_date)="' . $month . '"')
                            ->orderBy('presences.presence_date', 'asc')
                            ->select('presences.id', 'employees.uuid', 'employees.id as employee_id', 'presences.clock_in', 'presences.clock_out',  'presences.presence_date')
                            ->get()
                            ->groupBy('precense_date');

                        foreach ($historyPresenceByMonth as $history => $presences) {

                            $timeOffs = DB::table('time_offs')
                                ->join('users', 'time_offs.accepted_by', '=', 'users.uuid')
                                ->join('employees', 'time_offs.employee_id', '=', 'employees.uuid')
                                ->where('employees.uuid', $request->employee_id)
                                ->whereRaw('YEAR(time_offs.start_date)="' . $year . '"')
                                ->whereRaw('MONTH(time_offs.start_date)="' . $month . '"')
                                ->select('time_offs.id', 'time_offs.uuid', 'users.email as accepted_by',  'time_offs.type', 'time_offs.start_date', 'time_offs.end_date', 'time_offs.status', 'time_offs.description', 'time_offs.document', 'time_offs.status', 'time_offs.total_days')
                                ->get();

                            $months[] = [
                                'year' => $year,
                                'month' => $monthTemplate[$month],
                                'presences' => $presences,
                                'time_offs' => $timeOffs,
                            ];
                        }
                    }
                }
            }
            $pdf = Pdf::loadView('presenceById', ['months' => $months, 'name' => $name])->setPaper('a4', 'portrait');
            $pdf->download()->getOriginalContent();

            $dateTimeNow = new DateTime();
            $dateNow = $dateTimeNow->format('Y-m-d_H-i-s');

            $pdfpath = 'presenceById_' . $dateNow . '_' .  $employeeId . '.pdf';
            Storage::disk('public')->put('/presence-recap/' . $pdfpath, $pdf->output());

            $pdfFullPath = asset('storage/presence-recap/' . $pdfpath);

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'data' => [
                    'url' => $pdfFullPath
                ],
                'message' => 'Data presensi pegawai berhasil ditemukan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'data' => [
                    'error' => $e->getMessage(),
                ],
                'message' => 'data presensi tidak ada pada database',
            ], 404);
        }
    }
}
