<?php

namespace App\Services;

use App\Http\Requests\AbilityRequest;
use App\Models\Ability;
use App\Models\Agent;
use Illuminate\Support\Facades\DB;

class AbilityService
{
    public function addAbilities(array $data, Agent $agent){
        return DB::transaction(function () use ($data, $agent){
            if ($agent->is_draft === 0) {
                return [
                    'body' => [
                        'message' => 'El agente ya tiene sus habilidades asignadas.',
                    ],
                    'code' => 409,
                ];
            }

            foreach ($data['abilities'] as &$ability) {
                $ability['icon'] = $ability['icon']
                ->store("agents/$agent->slug", 'public');
            }

            // Crear las 4 nuevas habilidades y relacionarlas con el agente
            foreach ($data['abilities'] as $ability) {
                Ability::create([
                    'agent_id' => $agent->id,
                    'name' => $ability['name'],
                    'icon' => $ability['icon'],
                    'description' => $ability['description'],
                ]);
            }
    
            // Habilitar el agente
            $agent->update([
                'available' => true,
                'is_draft' => false,
            ]);
    
            return [
                'body' => [
                    'agent' => $agent->load('abilities', 'nationality', 'class'),
                    'message' => 'Habilidades agregadas correctamente. El agente está listo para servir en VALORANT.',
                ],
                'code' => 201,
            ];
        });
    }

    public function updateAbility(array $data, Ability $ability){
        $slug = $ability->agent->slug;

        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'],
        ];

        // Agregar icono de habilidad al array updateData solo si viene entre los datos
        if (isset($data['icon'])) {
            $updateData['icon'] = $data['icon']->store("agents/$slug", 'public');
        }
        
        //ACtualizar la habilidad
        $ability->update($updateData);

        $agent = $ability->agent;
        
        return $agent->load('abilities', 'nationality', 'class');
    }
}