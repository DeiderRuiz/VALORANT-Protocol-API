<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgentDraftRequest;
use App\Http\Requests\AgentRequest;
use App\Http\Requests\UpdateAgentPhotoRequest;
use App\Http\Requests\UpdateAgentProfileRequest;
use App\Http\Requests\UpdateProfilePhotoRequest;
use App\Models\Agent;
use App\Models\AgentClasses;
use App\Services\AgentService;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    protected $agentService;

    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }
    
    // Obtener todos los agentes (excepto los marcados como borrador)
    public function getAgents(){
        $agents = Agent::with(['abilities', 'nationality', 'class'])->where('is_draft', false)->get();
        return response()->json($agents, 200);
    }

    // Obtener borradores de agentes
    public function getAgentsDraft(){
        $agents = Agent::with(['user:id,email', 'nationality', 'class'])->where('is_draft', true)->get();
        return response()->json($agents, 200);
    }

    // Comprobar si un borrador de agente existe
    public function agentDraftExists(Agent $agent){
        $agent->load('user:id,email', 'nationality', 'class')->first();
        if ($agent->is_draft === 0) {
            return response()->json([
                'message' => 'El agente solicitado ya fue registrado.'
            ], 404);
        }
        return response()->json($agent, 200);
    }

    // Obtener todos los agentes y sus estadisticas en un mapa y tipo de misión especifica
    // Se muestran en la selección de agentes al crear o editar una misión
    public function getAgentsWithStats($map, $missionType){
        $agents = $this->agentService->getAgentsWithStats($map, $missionType);
        return response()->json($agents, 200);
    }

    // Obtener las clases de lod agentes (Roles  de duelista, centinela, controlador e iniciador)
    public function getAgentClasses(){
        $classes = AgentClasses::get();
        return response()->json($classes, 200);
    }

    // Mostrar un agente en especifico
    public function showAgent(Agent $agent){
        if ($agent->is_draft === 1) {
            return response()->json(404);
        }
        return response()->json($agent->load('abilities', 'nationality', 'class'), 200);
    }

    // Crear un nuevo agente (sin habilidades y en borrador por defecto)
    public function createDraftAgent(AgentRequest $request){
        try {
            $data = $request->validated();

            $data['profile_photo'] = $request->file('profile_photo');
            $data['agent_photo'] = $request->file('agent_photo');

            $agent = $this->agentService->createAgent($data);

            return response()->json([
                'message' => 'Agente registrado correctamente. Como siguiente paso, agrega las 4 habilidades para completar su perfil.',
                'agent' => $agent
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    // Crear un nuevo agente (sin habilidades y en borrador por defecto)
    public function updateDraftAgent(AgentDraftRequest $request, Agent $agent){
        try {
            $data = $request->validated();

            if ($request->hasFile('profile_photo')) {
                $data['profile_photo'] = $request->file('profile_photo');
            }

            if ($request->hasFile('agent_photo')) {
                $data['agent_photo'] = $request->file('agent_photo');
            }

            $agent = $this->agentService->updateNewAgent($data, $agent);

            return response()->json([
                'message' => 'Agente registrado correctamente. Como siguiente paso, agrega las 4 habilidades para completar su perfil.',
                'agent' => $agent
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    // Actualizar foto de perfil
    public function updateProfilePhoto(UpdateProfilePhotoRequest $request, Agent $agent){
        $data = $request->validated();

        $data['profile_photo'] = $request->file('profile_photo');
        $this->agentService->updateProfilePhoto($data, $agent);

        return response()->json([
            'message' => 'Foto de perfil actualizada correctamente.',
            'agent' => $agent
        ], 200);
    }

    // Actualizar foto del agente
    public function updateAgentPhoto(UpdateAgentPhotoRequest $request, Agent $agent){
        $data = $request->validated();

        $data['agent_photo'] = $request->file('agent_photo');
        $this->agentService->updateAgentPhoto($data, $agent);

        return response()->json([
            'message' => 'Imagen del agente actualizada correctamente.',
            'agent' => $agent
        ], 200);
    }

    // Actualizar perfil de agente (nombre, bio, nacionalidad, etc)
    public function updateAgentProfile(UpdateAgentProfileRequest $request, Agent $agent){
        $data = $request->validated();

        $profile = $this->agentService->updateAgentProfile($data, $agent);

        return response()->json([
            'message' => 'Perfil de agente actualizado correctamente.',
            'agent' => $profile
        ], 201);
    }
}
