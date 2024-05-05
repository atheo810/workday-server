<?php

namespace Database\Seeders;

use App\Models\TimeOff;
use CaliCastle\Cuid;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimeOffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TimeOff::create([
            'id' => 1,
            'uuid' => Cuid::make(),
            'accepted_by' => 1,
            'employee_id' => 'clvsssbqo0001cuglboduvoc4',
            'type' => 'annual',
            'description' => 'Annual leave',
            'start_date' => '2021-01-01',
            'end_date' => '2021-01-03',
            'total_days' => 3,
            'status' => 'approved',
        ]);
        TimeOff::create([
            'id' => 2,
            'uuid' => Cuid::make(),
            'accepted_by' => 1,
            'employee_id' => 'clvsssbqo0001cuglboduvoc4',
            'type' => 'annual',
            'description' => 'Annual leave',
            'start_date' => '2021-01-05',
            'end_date' => '2021-01-07',
            'total_days' => 2,
            'status' => 'approved',
        ]);
        TimeOff::create([
            'id' => 3,
            'uuid' => Cuid::make(),
            'accepted_by' => 1,
            'employee_id' => 'clvsssbqo0001cuglboduvoc4',
            'type' => 'annual',
            'description' => 'Annual leave',
            'start_date' => '2021-01-09',
            'end_date' => '2021-01-11',
            'total_days' => 2,
            'status' => 'approved',
        ]);
        TimeOff::create([
            'id' => 4,
            'uuid' => Cuid::make(),
            'accepted_by' => 1,
            'employee_id' => 'clvsssbqo0001cuglboduvoc5',
            'type' => 'annual',
            'description' => 'Annual leave',
            'start_date' => '2021-01-13',
            'end_date' => '2021-01-12',
            'total_days' => 1,
            'status' => 'approved',
        ]);
    }
}
