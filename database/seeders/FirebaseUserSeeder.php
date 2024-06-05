<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\FirebaseServices;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FirebaseUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentAdmin = User::join('employees', 'users.employee_id', '=', 'employees.uuid')
            ->select([
                'users.id as user_id', 'users.email', 'users.account_status',
                'employees.fullname', 'employees.position', 'employees.avatar'
            ])
            ->where('users.id', 1)
            ->first();

        $currentOwner = User::join('employees', 'users.employee_id', '=', 'employees.uuid')
            ->select([
                'users.id as user_id', 'users.email', 'users.account_status',
                'employees.fullname', 'employees.position', 'employees.avatar'
            ])
            ->where('users.id', 2)
            ->first();

        FirebaseServices::insertNew('users', $currentAdmin->toArray());
        FirebaseServices::insertNew('users', $currentOwner->toArray());
    }
}
