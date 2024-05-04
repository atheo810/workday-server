<?php

namespace Database\Seeders;

use App\Helpers\Text;
use App\Models\Employee;
use App\Models\Presence;
use CaliCastle\Cuid;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeData = [
            [
                'id' => 1,
                'uuid' => Cuid::make(),
                'fullname' => 'Jack Sparrow',
                'gender' => 'laki-laki',
                'position' => 'owner',
                'phone_number' => Text::formatPhoneNumber('087887556644'),
                'employee_code' => Text::generateIdCardNumber(),
                'avatar' => null,
                'address' => 'Jl. Raya No. 1, Jakarta',
                'joined_company_at' => now(),
                'status' => 'active'
            ],
            [
                'id' => 2,
                'uuid' => Cuid::make(),
                'fullname' => 'Super Admin',
                'gender' => 'laki-laki',
                'position' => 'administrator',
                'phone_number' => Text::formatPhoneNumber('081234567890'),
                'employee_code' => Text::generateIdCardNumber(),
                'avatar' => null,
                'address' => 'Jl. Raya No. 1, Jakarta',
                'joined_company_at' => now(),
                'status' => 'active'
            ],
            [
                'id' => 3,
                'uuid' => Cuid::make(),
                'fullname' => 'Ketut Sujana',
                'gender' => 'laki-laki',
                'position' => 'mechanic',
                'phone_number' => Text::formatPhoneNumber('089889339223'),
                'employee_code' => Text::generateIdCardNumber(),
                'avatar' => null,
                'address' => 'Jl. Raya No. 1, Jakarta',
                'joined_company_at' => now(),
                'status' => 'active'
            ]
        ];

        foreach ($employeeData as $employee) {
            $createdEmployee = Employee::create($employee);
            sleep(2);
        }
    }
}
