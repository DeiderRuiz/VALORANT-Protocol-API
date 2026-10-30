<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbilityRequest;
use App\Http\Requests\UpdateAbilityRequest;
use App\Models\Ability;
use App\Models\Agent;
use App\Services\AbilityService;

class AbilityController extends Controller
{
    protected $abilityService;

    public function __construct(AbilityService $abilityService)
    {
        $this->abilityService = $abilityService;
    }

    // Agregar 4 habilidades a un agente
    public function addAbilities(AbilityRequest $request, Agent $agent){
        $data = $request->validated();
        
        foreach ($data['abilities'] as $index => &$ability) {
            $ability['icon'] = $request->file("abilities.$index.icon");
        }

        $agent = $this->abilityService->addAbilities($data, $agent);

        return response()->json($agent['body'], $agent['code']);
    }

    // Actualizar una habilidad de agente
    public function updateAbility(UpdateAbilityRequest $request, Ability $ability){
        $data = $request->validated();

        if ($request->hasFile('icon')) {
            // Las imágenes van en una carpeta con el slug del agente
            $data['icon'] = $request->file('icon');
        }
        $abilityUpdated = $this->abilityService->updateAbility($data, $ability);

        return response()->json([
            'message' => 'Habilidad actualizada correctamente.',
            'agent' => $abilityUpdated
        ], 201);
    }
}
