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
            'uuid' => 'clvsssbqo0001cuglboduvob4',
            'email' => 'owner@gmail.com',
            'password' => Hash::make('password'),
            'account_status' => 'active',
            'employee_id' => 'clvsssbqo0001cuglboduvoc4',
        ]);

        User::create([
            'id' => 2,
            'uuid' => 'clvsssbqo0001cuglboduvob5',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'account_status' => 'active',
            'employee_id' => 'clvsssbqo0001cuglboduvoc5',
        ]);
    }
}
