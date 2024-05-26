<?php

namespace App\Http\Controllers;

use App\Models\TimeOff;
use CaliCastle\Cuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PhpParser\Node\Stmt\TryCatch;

class TimeOffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $timeOff = DB::table('time_offs')
                ->get([
                    'id', 'uuid', 'accepted_by', 'type', 'start_date',
                    'end_date', 'description', 'document', 'status', 'total_days'
                ]);

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
                'file' => 'required|file|mimes:pdf,jpeg,jpg,png|max:4096',
            ]);

            $file = $request->file('file');
            $fileName = Cuid::make();
            $resultFile = $file
                ? $file->storeAs('public/photos/employee-timeoff', "{$fileName}.{$file->extension()}")
                : null;

            $baseUrl = url(Storage::url($resultFile));

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
                'type' => 'required|in:annual,unpaid,sick_without_docs,sick_with_docs,holiday,special_permit',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'description' => 'required|string',
                'document' => 'nullable|string',
                'total_days' => 'required|integer',
            ]);

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
                'total_days' => $request->total_days,
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
    public function show(string $employeeId)
    {
        try {
            $timeOff = DB::table('time_offs')
                ->select([
                    'uuid', 'accepted_by', 'type', 'start_date', 'employee_id',
                    'end_date', 'description', 'document', 'status', 'total_days'
                ])
                ->where('employee_id', $employeeId)
                ->get();

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
                'type' => 'required|in:annual,unpaid,sick_without_docs,sick_with_docs,holiday,special_permit',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'description' => 'required|string',
                'document' => 'nullable|string',
                'total_days' => 'required|integer',
            ]);

            $timeOff = TimeOff::where('uuid', $uuid)->get()->first();

            if (!$timeOff) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Data cuti tidak ditemukan.',
                    'data' => null,
                ], 404);
            }

            $timeOff->update([
                'employee_id' => (string) $request->employee_id,
                'accepted_by' => (string) $request->accepted_by,
                'type' => strtolower($request->type),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'document' => $request->document,
                'total_days' => (int) $request->total_days,
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeOff $timeOff)
    {
        //
    }

    // Recovery Data
    public function recovery(string $timeOff)
    {
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

            if (!$timeOff) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Data gagal di update.',
                    'data' => null,
                ], 404);
            }
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
