<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Map;
use App\Models\Mission;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getDashboardData(Agent $agent){
        
        return DB::transaction(function () use ($agent){
            // Cantidad de misiones completas (Exitosas y fallidas)
            $myMissionsCompleted = $agent->missions()->where('statement', 'completed')->count();

            // Cantidad de misiones exitosas
            $myVictories = $agent->missions()->where('statement', 'completed')
            ->where('result', 'success')->count();

            // Promedio de victorias
            $myVictoryRate = $myMissionsCompleted > 0 ? round(($myVictories / $myMissionsCompleted) * 100, 2) : 0;

            //Cantidad de agentes
            $agentsQuantity = Agent::where('is_draft', false)->count();

            // Cantidad de mapas
            $mapsQuantity = Map::count();

            // Obtener la misión más reciente a la que fue asignado el agente logeado
            $myLastMission = $agent->missions()
            ->select(
                'missions.id', 'missions.title', 'missions.statement', 
                'missions.result', 'missions.created_at', 'missions.updated_at'
                )
            ->orderBy('missions.created_at', 'desc')->first();

            // Obtener la misión más reciente creada
            $lastMission = Mission::select(
                'missions.id', 'missions.title', 'missions.statement', 
                'missions.result', 'missions.created_at', 'missions.updated_at'
                )
            ->orderBy('missions.created_at', 'desc')->first();

            // ESTADISTICAS GENERALES
            $missionsCompleted = Mission::where('statement', 'completed')->count();
            $victories = Mission::where('statement', 'completed')->where('result', 'success')->count();
            $defeats = Mission::where('statement', 'completed')->where('result', 'failed')->count();
            $victoryRate = $missionsCompleted > 0 ? round(($victories / $missionsCompleted) * 100, 2) : 0;

            return [
                'profile' => [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'profile_photo' => $agent->profile_photo,
                    'class' => $agent->class->name,
                ],
                'myStatistics' => [
                    'missionsCompleted' => $myMissionsCompleted,
                    'victories' => $myVictories,
                    'victoryRate' => $myVictoryRate,
                ],
                'agents' => $agentsQuantity,
                'maps' => $mapsQuantity,
                'myLastMission' => $myLastMission,
                'lastMission' => $lastMission,
                'generalStatistics' => [
                    'missionsCompleted' => $missionsCompleted,
                    'victories' => $victories,
                    'defeats' => $defeats,
                    'victoryRate' => $victoryRate,
                ],
            ];
        });
    }
}