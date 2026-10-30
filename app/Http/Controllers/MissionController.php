<?php

namespace App\Http\Controllers;

use App\Http\Requests\MissionDetailsRequest;
use App\Http\Requests\MissionRequest;
use App\Http\Requests\MissionStatusRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\UpdateMissionAgentsRequest;
use App\Models\Mission;
use App\Models\MissionType;
use App\Services\MissionService;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    protected $missionService;

    public function __construct(MissionService $missionService)
    {
        $this->missionService = $missionService;
    }

    // Obtener todas las misiones en general
    public function getMissions(){
        $missions = Mission::with(['agents', 'map', 'type'])->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($missions, 200);
    }

    // Obtener solo misiones donde participó el agente logeado
    public function getMyMissions(Request $request){
        $agent = $request->user()->agent;
        $missions = $agent->missions()->with(['agents', 'map', 'type'])->orderBy('missions.created_at', 'desc')->paginate(2);
        return response()->json($missions, 200);
    }

    // Detalles de la misión
    public function showMission(Mission $mission){
        return response()->json($mission->load('agents.class', 'map', 'type', 'leader', 'events.agent', 'report'), 200);
    }

    // Tipos de Mision
    public function missionTypes(){
        $missionTypes = MissionType::get();
        return response()->json($missionTypes, 200);
    }

    // Buscar misiones
    public function searchMissions(SearchRequest $request){
        $missions = $this->missionService->searchMissions($request->validated());
        return response()->json($missions, 200);
    }

    // Validar campos del paso 1 (detalles de misión) al crear una misión
    public function validateMission(MissionDetailsRequest $request){
        $request->validated();
        return response()->json(['valid' => true]);
    }

    // Crear la misión, junta los datos de los pasos 1 y 2 (detalles de misión y agentes asignados)
    public function newMission(MissionRequest $request){
        $mission = $this->missionService->newMission($request->validated());
        
        return response()->json([
            'message' => 'Misión creada correctamente.',
            'mission' => $mission
        ], 200);
    }

    // Editar estado de misión
    public function editMissionStatus(MissionStatusRequest $request, Mission $mission){
        $missionUpdated = $this->missionService->editMissionStatus($request->validated(), $mission);
        return response()->json($missionUpdated['body'], $missionUpdated['code']);
    }

    // Editar los detalles de una misión
    public function updateMissionDetails(MissionDetailsRequest $request, Mission $mission){
        $missionUpdated = $this->missionService->updateMissionDetails($request->validated(), $mission);
        return response()->json($missionUpdated['body'], $missionUpdated['code']);
    }

    // Editar los agentes asignados a una misión
    public function updateMissionAgents(UpdateMissionAgentsRequest $request, Mission $mission){
        $missionUpdated = $this->missionService->updateMissionAgents($request->validated(), $mission);
        return response()->json($missionUpdated['body'], $missionUpdated['code']);
    }
}
