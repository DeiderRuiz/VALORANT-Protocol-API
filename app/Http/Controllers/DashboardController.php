<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Map;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    // Obtener datos para el Dashboard
    public function index( Request $request){
        // Agente logeado
        $agent = $request->user()->agent;
        $getData = $this->dashboardService->getDashboardData($agent);
        
        return response()->json($getData, 200);
    }
}
