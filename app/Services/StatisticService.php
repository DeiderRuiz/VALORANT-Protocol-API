<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Map;
use App\Models\Mission;
use App\Models\MissionType;

class StatisticService
{
    public function getStatistics(){
        // ESTADISTICAS GENERALES
        $missionsCreated = Mission::count();
        $missionsCompleted = Mission::where('statement', 'completed')->count();
        $missionsAborted = Mission::where('statement', 'aborted')->count();
        $victories = Mission::where('statement', 'completed')->where('result', 'success')->count();
        $defeats = Mission::where('statement', 'completed')->where('result', 'failed')->count();
        $victoryRate = $missionsCompleted > 0 ? round(($victories / $missionsCompleted) * 100, 2) : 0;

        // ESTADISTICAS POR MAPA
        $maps = Map::get();
        $mapStats = [];
        foreach ($maps as $map) {
            $mapMissionsCompleted = $map->missions()->where('statement', 'completed')->count();
            $mapMissionsAborted = $map->missions()->where('statement', 'aborted')->count();
            $mapVictories = $map->missions()->where('statement', 'completed')->where('result', 'success')->count();
            $mapDefeats = $map->missions()->where('statement', 'completed')->where('result', 'failed')->count();
            $mapVictoryRate = $mapMissionsCompleted > 0 ? round(($mapVictories / $mapMissionsCompleted) * 100, 2) : 0;

            if ($mapMissionsCompleted > 0) {
                $mapStats[] = [
                    'itemName' => $map->name,
                    'missionsCompleted' => $mapMissionsCompleted,
                    'missionsAborted' => $mapMissionsAborted,
                    'victories' => $mapVictories,
                    'defeats' => $mapDefeats,
                    'victoryRate' => $mapVictoryRate,
                ];
            }
        }

        // ESTADISTICAS POR TIPO DE MISION
        $missionTypes = MissionType::get();
        $missionTypeStats = [];
        foreach ($missionTypes as $missionType) {
            $missionTypesCompleted = $missionType->mission()->where('statement', 'completed')->count();
            $missionTypesMissionsAborted = $missionType->mission()->where('statement', 'aborted')->count();
            $missionTypesVictories = $missionType->mission()->where('statement', 'completed')->where('result', 'success')->count();
            $missionTypesDefeats = $missionType->mission()->where('statement', 'completed')->where('result', 'failed')->count();
            $missionTypesVictoryRate = $missionTypesCompleted > 0 ? round(($missionTypesVictories / $missionTypesCompleted) * 100, 2) : 0;

            if ($missionTypesCompleted > 0) {
                $missionTypeStats[] = [
                    'itemName' => $missionType->name,
                    'missionsCompleted' => $missionTypesCompleted,
                    'missionsAborted' => $missionTypesMissionsAborted,
                    'victories' => $missionTypesVictories,
                    'defeats' => $missionTypesDefeats,
                    'victoryRate' => $missionTypesVictoryRate,
                ];
            }
        }

        return [
            'general' => [
                'missionsCreated' => $missionsCreated,
                'missionsCompleted' => $missionsCompleted,
                'missionsAborted' => $missionsAborted,
                'victories' => $victories,
                'defeats' => $defeats,
                'victoryRate' => $victoryRate,
            ],
            'mapStatistics' => $mapStats,
            'missionTypeStatistics' => $missionTypeStats
        ];
    }

    public function getAgentsStatistics(Agent $agent){
        // ESTADISTICAS GENERALES
        $missionsCreated = $agent->missions()->count();
        $missionsAsLeader = $agent->missions()->where('leader_id', $agent->id)->count();
        $missionsCompleted = $agent->missions()->where('statement', 'completed')->count();
        $missionsAborted = $agent->missions()->where('statement', 'aborted')->count();
        $victories = $agent->missions()->where('statement', 'completed')->where('result', 'success')->count();
        $defeats = $agent->missions()->where('statement', 'completed')->where('result', 'failed')->count();
        $victoryRate = $missionsCompleted > 0 ? round(($victories / $missionsCompleted) * 100, 2) : 0;

        // ESTADISTICAS POR MAPA
        $maps = Map::get();
        $mapStats = [];
        foreach ($maps as $map) {
            $mapMissionsCompleted = $agent->missions()->where('map_id', $map->id)->where('statement', 'completed')->count();
            $mapMissionsAborted = $agent->missions()->where('map_id', $map->id)->where('statement', 'aborted')->count();
            $mapVictories = $agent->missions()->where('map_id', $map->id)->where('statement', 'completed')->where('result', 'success')->count();
            $mapDefeats = $agent->missions()->where('map_id', $map->id)->where('statement', 'completed')->where('result', 'failed')->count();
            $mapVictoryRate = $mapMissionsCompleted > 0 ? round(($mapVictories / $mapMissionsCompleted) * 100, 2) : 0;

            if ($mapMissionsCompleted > 0) {
                $mapStats[] = [
                    'itemName' => $map->name,
                    'missionsCompleted' => $mapMissionsCompleted,
                    'missionsAborted' => $mapMissionsAborted,
                    'victories' => $mapVictories,
                    'defeats' => $mapDefeats,
                    'victoryRate' => $mapVictoryRate,
                ];
            }
        }

        // ESTADISTICAS POR TIPO DE MISION
        $missionTypes = MissionType::get();
        $missionTypeStats = [];
        foreach ($missionTypes as $missionType) {
            $missionTypesCompleted = $agent->missions()->where('type_id', $missionType->id)->where('statement', 'completed')->count();
            $missionTypesMissionsAborted = $agent->missions()->where('type_id', $missionType->id)->where('statement', 'aborted')->count();
            $missionTypesVictories = $agent->missions()->where('type_id', $missionType->id)->where('statement', 'completed')->where('result', 'success')->count();
            $missionTypesDefeats = $agent->missions()->where('type_id', $missionType->id)->where('statement', 'completed')->where('result', 'failed')->count();
            $missionTypesVictoryRate = $missionTypesCompleted > 0 ? round(($missionTypesVictories / $missionTypesCompleted) * 100, 2) : 0;

            if ($missionTypesCompleted > 0) {
                $missionTypeStats[] = [
                    'itemName' => $missionType->name,
                    'missionsCompleted' => $missionTypesCompleted,
                    'missionsAborted' => $missionTypesMissionsAborted,
                    'victories' => $missionTypesVictories,
                    'defeats' => $missionTypesDefeats,
                    'victoryRate' => $missionTypesVictoryRate,
                ];
            }
        }

        // ESTADISTICAS CON OTROS AGENTES
        $agents = Agent::get();
        $agentStats = [];

        foreach ($agents as $agente) {
            $missionAgentsCompleted = $agent->missions()->where('statement', 'completed')
            ->whereHas('agents', function ($query) use ($agente) {
                $query->where('agents.id', $agente->id);
            })->count();
            $missionAgentsAborted = $agent->missions()->where('statement', 'aborted')
            ->whereHas('agents', function ($query) use ($agente) {
                $query->where('agents.id', $agente->id);
            })->count();
            $missionAgentsVictories = $agent->missions()->where('statement', 'completed')->where('result', 'success')
            ->whereHas('agents', function ($query) use ($agente) {
                $query->where('agents.id', $agente->id);
            })->count();
            $missionAgentsDefeats = $agent->missions()->where('statement', 'completed')->where('result', 'failed')
            ->whereHas('agents', function ($query) use ($agente) {
                $query->where('agents.id', $agente->id);
            })->count();
            $missionAgentsVictoryRate = $missionAgentsCompleted > 0 ? round(($missionAgentsVictories / $missionAgentsCompleted) * 100, 2) : 0;

            if ($missionAgentsCompleted > 0 && $agent->id !== $agente->id) {
                $agentStats[] = [
                    'itemName' => $agente->name,
                    'missionsCompleted' => $missionAgentsCompleted,
                    'missionsAborted' => $missionAgentsAborted,
                    'victories' => $missionAgentsVictories,
                    'defeats' => $missionAgentsDefeats,
                    'victoryRate' => $missionAgentsVictoryRate,
                ];
            }
        }

        return [
            'general' => [
                'missionsCreated' => $missionsCreated,
                'missionsAsLeader' => $missionsAsLeader,
                'missionsCompleted' => $missionsCompleted,
                'missionsAborted' => $missionsAborted,
                'victories' => $victories,
                'defeats' => $defeats,
                'victoryRate' => $victoryRate,
            ],
            'mapStatistics' => $mapStats,
            'missionTypeStatistics' => $missionTypeStats,
            'agentStats' => $agentStats
        ];
    }
}