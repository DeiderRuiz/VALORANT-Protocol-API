<?php

namespace Database\Seeders;

use App\Models\AgentClasses;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AgentClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clases = [
            [
                'name' => 'Controlador',
                'icon' => 'ControllerClassSymbol.webp',
                'description' => 'Los controladores son expertos en cortar territorios peligrosos para preparar a su equipo para el éxito.',
            ],
            [
                'name' => 'Centinela',
                'icon' => 'SentinelClassSymbol.webp',
                'description' => 'Los centinelas son expertos defensivos que pueden bloquear áreas y vigilar los flancos.',
            ],
            [
                'name' => 'Iniciador',
                'icon' => 'InitiatorClassSymbol.webp',
                'description' => 'Los iniciadores desafían los ángulos preparando a su equipo para entrar en terreno disputado.',
            ],
            [
                'name' => 'Duelista',
                'icon' => 'DuelistClassSymbol.webp',
                'description' => 'Los duelistas son fraggers autosuficientes que buscan enfrentamientos primero.',
            ]
        ];

        foreach ($clases as $clase) {

            $iconPath = 'roles/' . $clase['icon'];

            Storage::disk('public')->put(
                $iconPath,
                file_get_contents(public_path('roles/' . $clase['icon']))
            );

            AgentClasses::create([
                'icon' => $iconPath,
                'name' => $clase['name'],
                'description' => $clase['description'],
            ]);
        }
    }
}
