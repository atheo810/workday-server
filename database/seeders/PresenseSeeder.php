<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Presence;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PresenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            Presence::factory()->create([
                'employee_id' => function () {
                    return Employee::inRandomOrder()->first()->uuid;
                }, // use the uuid of the created Employee
            ]);
        }
    }
}
