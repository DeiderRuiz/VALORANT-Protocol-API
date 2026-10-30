<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = DB::table('roles')->pluck('id', 'name');

        $users = json_decode(file_get_contents(database_path('data/users.json')), true);

        foreach ($users as $user) {
            User::create([
                'uuid' => Str::uuid()->toString(),
                'email' => $user['email'],
                'password' => bcrypt($user['password']),
                'role_id' => $role[$user['role_name']],
            ]);
        }
    }
}
