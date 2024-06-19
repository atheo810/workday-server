<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([EmployeeSeeder::class, UserSeeder::class]);
        // , PresenseSeeder::class,
        $this->call(PermissionSeeder::class);
        // $this->call(TimeOffSeeder::class);
        $this->call(LocationOfficeSeeder::class);
    }
}
