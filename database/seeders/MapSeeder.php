<?php

namespace Database\Seeders;

use App\Models\Map;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maps = json_decode(file_get_contents(database_path('data/maps.json')), true);

        foreach ($maps as $map) {
            $photoPath = 'maps/' . basename($map['photo']);

            // copiar la imagen al storage público
            Storage::disk('public')->put(
                $photoPath,
                file_get_contents(public_path('maps/' . basename($map['photo'])))
            );

            Map::create([
                'name' => $map['name'],
                'photo' => $photoPath,
                'description' => $map['description'],
            ]);
        }
    }
}
