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
        MissionType::firstOrCreate(['name' => 'ataque']);
        MissionType::firstOrCreate(['name' => 'defensa']);
        MissionType::firstOrCreate(['name' => 'inteligencia']);
        MissionType::firstOrCreate(['name' => 'emergencia']);
    }
}
