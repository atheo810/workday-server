<?php

namespace Database\Seeders;

use App\Models\LocationOffice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationOfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LocationOffice::create([
            'location_name' => 'Eurocar Service',
            'location_address' => 'Jl. Bypass Ngurah Rai No.95, Sanur, Denpasar Selatan, Kota Denpasar, Bali 80228',
            'latitude' => '-8.6928216',
            'longitude' => '115.2559701',
            'is_edit' => false
        ]);
    }
}
