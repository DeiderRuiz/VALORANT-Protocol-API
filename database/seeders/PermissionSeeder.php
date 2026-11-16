<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'create users']);
        Permission::firstOrCreate(['name' => 'create abilities']);
        Permission::firstOrCreate(['name' => 'create maps']);
        Permission::firstOrCreate(['name' => 'create missions']);
        Permission::firstOrCreate(['name' => 'edit abilities']);
        Permission::firstOrCreate(['name' => 'edit maps']);
        Permission::firstOrCreate(['name' => 'edit missions']);
    }
}
