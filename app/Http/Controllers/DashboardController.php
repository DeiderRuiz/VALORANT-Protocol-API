<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Map;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    // Obtener datos para el Dashboard
    public function index( Request $request){
        try {
            $user = $request->user();
            $agent = $user->agent;

            Log::info('DASHBOARD: Agente obtenido', [
                'user_id' => $user->id,
                'agent_id' => $agent?->id,
                'class_id' => $agent?->class_id,
            ]);
            // Agente logeado
            /* $agent = $request->user()->agent; */
            $getData = $this->dashboardService->getDashboardData($agent);
            Log::info('DASHBOARD: Datos obtenidos correctamente');
            return response()->json($getData, 200);
        } catch (\Throwable $e) {
            Log::error('ERROR EN DASHBOARD', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
