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
    // try catch validate the request
    try {
      $request->validate([
        'location_name' => 'required|string',
        'location_address' => 'required|string',
        'latitude' => 'required|string',
        'longitude' => 'required|string',
        'is_edit' => 'required|boolean'
      ]);

      // Check if there is no a record in the database
      if (!LocationOffice::first()) {
        // create data location office
        $locationOffice = LocationOffice::create([
          'location_name' => $request->location_name,
          'location_address' => $request->location_address,
          'latitude' => $request->latitude,
          'longitude' => $request->longitude,
        ]);

        // return response if location office is created
        return response()->json([
          'code' => 201,
          'status' => 'success',
          'data' => $locationOffice,
          'message' => 'Lokasi Kantor Berhasil dibuat.'
        ], 200);
      }

      $locationOffice = LocationOffice::updateOrCreate(
        ['id' => 1],
        [
          'location_name' => $request->location_name,
          'location_address' => $request->location_address,
          'latitude' => $request->latitude,
          'longitude' => $request->longitude,
          'is_edit' => $request->is_edit
        ]
      );
      // Return response if location office is created or updated
      return response()->json([
        'code' => 200,
        'status' => 'success',
        'data' => $locationOffice,
        'message' => 'Lokasi kantor berhasil diperbarui.'
      ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'code' => 422,
        'status' => 'error',
        'errors' => $e->errors(),
        'message' => 'Data yang diberikan tidak valid.'
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'code' => 500,
        'status' => 'error',
        'error' => $e->getMessage(),
        'message' => 'Gagal memperbarui lokasi kantor.'
      ], 500);
    }
  }

  public function update(Request $request, string $locationOffice)
  {
    $createLocationOffice = DB::table('location_offices')->find($locationOffice);

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

    try {
      DB::table('location_offices')
        ->updateOrInsert(
          ['id' => $createLocationOffice->id],
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
}
