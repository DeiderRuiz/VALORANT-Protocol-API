<?php

namespace Database\Seeders;

use App\Models\Ability;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AbilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agents = DB::table('agents')->pluck('id', 'name');

        $abilities = json_decode(file_get_contents(database_path('data/abilities.json')), true);

        foreach ($abilities as $ability) {
            $slug = Str::slug($ability['agent_name']);

            $iconPath = 'agents/' . $slug . '/' . basename($ability['icon']);

            // copiar la imagen al storage público
            Storage::disk('public')->put(
                $iconPath,
                file_get_contents(public_path('agents/' . $slug . '/' . basename($ability['icon'])))
            );

            Ability::create([
                'agent_id' => $agents[$ability['agent_name']],
                'icon' => $iconPath,
                'name' => $ability['name'],
                'description' => $ability['description']
            ]);
        }
    }
}
