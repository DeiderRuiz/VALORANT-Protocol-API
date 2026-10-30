<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('id', 'name');
        $permissions = DB::table('permissions')->pluck('id', 'name');

        DB::table('role_permissions')->insert([
            ['role_id' => $roles['admin'], 'permission_id' => $permissions['create users']],
            ['role_id' => $roles['admin'], 'permission_id' => $permissions['create abilities']],
            ['role_id' => $roles['admin'], 'permission_id' => $permissions['create maps']],
            ['role_id' => $roles['admin'], 'permission_id' => $permissions['create missions']],
            ['role_id' => $roles['admin'], 'permission_id' => $permissions['edit abilities']],
            ['role_id' => $roles['admin'], 'permission_id' => $permissions['edit maps']],
            ['role_id' => $roles['admin'], 'permission_id' => $permissions['edit missions']],

            ['role_id' => $roles['assistant'], 'permission_id' => $permissions['create missions']],
            ['role_id' => $roles['assistant'], 'permission_id' => $permissions['edit missions']],
        ]);
    }
}
