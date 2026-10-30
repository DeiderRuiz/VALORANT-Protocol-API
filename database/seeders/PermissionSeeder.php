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
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'create abilities']);
        Permission::create(['name' => 'create maps']);
        Permission::create(['name' => 'create missions']);
        Permission::create(['name' => 'edit abilities']);
        Permission::create(['name' => 'edit maps']);
        Permission::create(['name' => 'edit missions']);
    }
}
