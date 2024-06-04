<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'guard_name' => 'api',
            'name' => 'administrator'
        ]);
        Role::create([
            'guard_name' => 'api',
            'name' => 'owner'
        ]);
        Role::create([
            'guard_name' => 'api',
            'name' => 'head-mechanic'
        ]);
        Role::create([
            'guard_name' => 'api',
            'name' => 'mechanic'
        ]);
        Role::create([
            'guard_name' => 'api',
            'name' => 'cleaner'
        ]);
        Role::create([
            'guard_name' => 'api',
            'name' => 'office-boy'
        ]);
        Role::create([
            'guard_name' => 'api',
            'name' => 'security'
        ]);

        $currentAdmin = User::find(1);
        $currentOwner = User::find(2);

        // $currentSecurity = User::find(3);

        $currentOwner->assignRole('owner');
        $currentAdmin->assignRole('administrator');
    }
}
