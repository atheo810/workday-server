<?php

namespace App\Http\Controllers;

use App\Models\LocationOffice;
use App\Models\Presence;
use CaliCastle\Cuid;
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
        $earthRadius = 6371000; // Radius bumi dalam meter
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
                'presence_by' => 'integer',
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
                    'presence_by' => (int) $createdBy,
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

    public function store(Request $request)
    {
    }
}
