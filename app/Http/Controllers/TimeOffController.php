<?php

namespace App\Http\Controllers;

use App\Models\TimeOff;
use CaliCastle\Cuid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TimeOffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $timeOff = DB::table('time_offs')
                ->select([
                    'id', 'uuid', 'accepted_by', 'type', 'start_date', 'timeoff_created_at',
                    'end_date', 'description', 'document', 'status', 'total_days'
                ])->get();

            if ($timeOff->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Tidak ada data cuti yang tersedia.',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Data cuti berhasil ditemukan.',
                'data' => $timeOff,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required'
            ]);

            $file = $request->file('file');

            $fileName = Cuid::make();
            $resultFile = $file
                ? $file->storeAs('photos/employee-timeoff', "{$fileName}.{$file->extension()}")
                : null;

            $baseUrl = Storage::url($resultFile);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Dokumen cuti berhasil diupload.',
                'data' => [
                    'url' => $baseUrl,
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'accepted_by' => 'string',
                'employee_id' => 'required|string',
                'type' => 'required|in:cuti,sakit,lembur,tugas_keluar',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'description' => 'required|string',
                'document' => 'nullable|string',
            ]);

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            if ($startDate->greaterThan($endDate)) {
                return response()->json([
                    'code' => 422,
                    'status' => 'error',
                    'message' => 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.',
                    'data' => null,
                ], 422);
            } else if ($endDate->lessThan($startDate)) {
                return response()->json([
                    'code' => 422,
                    'status' => 'error',
                    'message' => 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.',
                    'data' => null,
                ], 422);
            }

            $totalDays = $endDate->diffInDays($startDate) + 1;

            $uuid = Cuid::make();
            TimeOff::create([
                'uuid' => $uuid,
                'employee_id' => $request->employee_id,
                'accepted_by' => $request->accepted_by,
                'type' => $request->type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'document' => $request->document,
                'total_days' => $totalDays,
            ]);

            return response()->json([
                'code' => 201,
                'status' => 'success',
                'message' => 'Data cuti berhasil ditambahkan.',
                'data' => null,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
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
     * Display the specified resource.
     */
    public function show(Request $request, string $employeeId)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        try {
            $query = DB::table('time_offs')
                ->select([
                    'uuid', 'accepted_by', 'type', 'start_date', 'employee_id',
                    'end_date', 'description', 'document', 'status', 'total_days'
                ])
                ->where('employee_id', $employeeId);

            $timeOff = $startDate && $endDate
                ? $query->whereBetween('timeoff_created_at', [$startDate, $endDate])->get()
                : $query->get();

            if ($timeOff->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Data cuti tidak ditemukan.',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Data cuti berhasil ditemukan.',
                'data' => $timeOff,
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        try {
            $request->validate([
                'accepted_by' => 'required|string',
                'employee_id' => 'required|string',
                'type' => 'required|in:cuti,sakit,lembur,tugas_keluar',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'description' => 'required|string',
                'document' => 'nullable|string',
            ]);

            $timeOff = TimeOff::where('uuid', $uuid)->first();

            if (!$timeOff) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Data cuti tidak ditemukan.',
                    'data' => null,
                ], 404);
            }

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            if ($startDate->greaterThan($endDate)) {
                return response()->json([
                    'code' => 422,
                    'status' => 'error',
                    'message' => 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.',
                    'data' => null,
                ], 422);
            } else if ($endDate->lessThan($startDate)) {
                return response()->json([
                    'code' => 422,
                    'status' => 'error',
                    'message' => 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.',
                    'data' => null,
                ], 422);
            }

            $totalDays = $endDate->diffInDays($startDate) + 1;

            $timeOff->update([
                'employee_id' => (string) $request->employee_id,
                'accepted_by' => (string) $request->accepted_by,
                'type' => strtolower($request->type),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'document' => $request->document,
                'total_days' => (int) $totalDays,
            ]);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Data cuti berhasil diupdate.',
                'data' => null,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
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

    public function validateStatusTimeOff(Request $request, string $uuid)
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,rejected,pending'
            ]);

            $timeOff = TimeOff::where('uuid', $uuid)->first();

            if (!$timeOff) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Data cuti tidak ditemukan.',
                    'data' => null,
                ], 404);
            }

            $timeOff->update([
                'status' => $request->status,
            ]);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Data cuti berhasil diupdate.',
                'data' => null,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
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
}
