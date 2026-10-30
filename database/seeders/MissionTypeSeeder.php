<?php

namespace Database\Seeders;

use App\Models\MissionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MissionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MissionType::create(['name' => 'ataque']);
        MissionType::create(['name' => 'defensa']);
        MissionType::create(['name' => 'inteligencia']);
        MissionType::create(['name' => 'emergencia']);
    }
}
