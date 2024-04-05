<?php

namespace App\Http\Controllers;

use App\Models\LocationOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationOfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locationOffices = LocationOffice::first();
        if ($locationOffices) {
            // Records were found in the database.
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $locationOffices,
                'message' => 'Lokasi kantor berhasil ditemukan.'
            ], 200);
        } else {
            // No records were found in the database.
            return response()->json([
                'code' => 404,
                'status' => 'not found',
                'data' => $locationOffices,
                'message' => 'Tidak ada lokasi kantor yang ditemukan.'
            ], 404);
        }
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

        // Check if there is already a record in the database
        if (LocationOffice::exists()) {
            return response()->json([
                'code' => 409,
                'status' => 'error',
                'message' => 'Tidak Bisa membuat data Kantor Lebih dari satu.'
            ], 409);
        }

        // try catch validate the request
        try {
            $request->validate([
                'location_name' => 'required|string',
                'location_address' => 'required|string',
                'latitude' => 'required|string',
                'longitude' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'errors' => [$e->errors()],
                'message' => 'Data yang diberikan tidak valid.'
            ], 422);
        }



        // create data location office
        $locationOffice = LocationOffice::create([
            'location_name' => $request->location_name,
            'location_address' => $request->location_address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // if location office is not created
        if (!$locationOffice) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => [
                    'location' => null,
                ],
                'message' => 'Gagal menambahkan lokasi kantor.'
            ], 500);
        }

        // return response if location office is created
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => $locationOffice,
            'message' => 'Location office has been created successfully.'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(LocationOffice $locationOffice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LocationOffice $locationOffice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $locationOffice)
    {
        // get location office
        $createLocationOffice = DB::table('location_offices')->find($locationOffice);

        // if location office is not found
        if (!$createLocationOffice) {
            return response()->json([
                'code' => 404,
                'status' => 'not found',
                'data' => [
                    'location' => null,
                ],
                'message' => 'Lokasi kantor tidak ditemukan.'
            ], 404);
        }

        // try catch validate the request
        try {
            $request->validate([
                'location_name' => 'required|string',
                'location_address' => 'required|string',
                'latitude' => 'required|string',
                'longitude' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'errors' => [$e->errors()],
                'message' => 'Data yang diberikan tidak valid.'
            ], 422);
        }



        // update location office
        try {
            DB::table('location_offices')
                ->updateOrInsert(
                    ['id' => $createLocationOffice->id], // Assuming $createLocationOffice is an instance of LocationOffice
                    [
                        'location_name' => $request->location_name,
                        'location_address' => $request->location_address,
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                    ]
                );
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'error' => $e->getMessage(),
                'message' => 'Gagal memperbarui lokasi kantor.'
            ], 500);
        }

        // return response if location office is updated
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => $createLocationOffice,
            'message' => 'Lokasi kantor berhasil diperbahrui.'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LocationOffice $locationOffice)
    {
        //
    }
}
