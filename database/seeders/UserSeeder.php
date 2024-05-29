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
            'uuid' => '0390f3125f5d5c93ea6d836ce382',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'account_status' => 'active',
            'employee_id' => 'clvsssbqo0001cuglboduvoc5',
        ]);

        User::create([
            'id' => 2,
            'uuid' => '0390f3125f5d5c93ea6d836ce381',
            'email' => 'owner@gmail.com',
            'password' => Hash::make('password'),
            'account_status' => 'active',
            'employee_id' => 'clvsssbqo0001cuglboduvoc4',
        ]);
    }
}
