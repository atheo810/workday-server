<?php

namespace Database\Seeders;

use App\Models\User;
use CaliCastle\Cuid;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'id' => 1,
            'uuid' => Cuid::make(),
            'email' => 'owner@gmail.com',
            'password' => Hash::make('password'),
            'account_status' => 'active',
            'employee_id' => 1,
        ]);

        User::create([
            'id' => 2,
            'uuid' => Cuid::make(),
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'account_status' => 'active',
            'employee_id' => 2,
        ]);
    }
}
