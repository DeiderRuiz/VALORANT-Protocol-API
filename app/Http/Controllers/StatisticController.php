<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Services\StatisticService;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    protected $statisticService;

    public function __construct(StatisticService $statisticService)
    {
        $this->statisticService = $statisticService;
    }

    // Obtener estadisticas en general
    public function getStatistics(){
        $general = $this->statisticService->getStatistics();
        return response()->json($general, 200);
    }

    // Obtener estadisticas de un agente en concreto
    public function getAgentStatistics(Agent $agent){
        $statistics = $this->statisticService->getAgentsStatistics($agent);
        return response()->json($statistics, 200);
    }

}
