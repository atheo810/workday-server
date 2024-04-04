<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate(
            ['name' => 'administrator'],
            ['name' => 'administrator'],
        );
        Role::updateOrCreate(
            ['name' => 'owner'],
            ['name' => 'owner']
        );
        Role::updateOrCreate(
            ['name' => 'head-mechanic'],
            ['name' => 'head-mechanic']
        );
        Role::updateOrCreate(
            ['name' => 'mechanic'],
            ['name' => 'mechanic']
        );
        Role::updateOrCreate(
            ['name' => 'cleaner'],
            ['name' => 'cleaner']
        );
        Role::updateOrCreate(
            ['name' => 'office-boy'],
            ['name' => 'office-boy']
        );
        Role::updateOrCreate(
            ['name' => 'security'],
            ['name' => 'security']
        );

        $currentOwner = User::find(1);
        $currentAdmin = User::find(2);

        $currentOwner->assignRole('owner');
        $currentAdmin->assignRole('administrator');
    }
}
