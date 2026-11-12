<?php

namespace Database\Seeders;

use App\Models\Nationality;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NationalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nationalities = json_decode(file_get_contents(database_path('data/countries2.json')), true);

        foreach ($nationalities as $nationality) {
            Nationality::create([
                'country_name' => $nationality['country_name'],
                'country_code' => $nationality['country_code'],
            ]);
        }
    }
}
