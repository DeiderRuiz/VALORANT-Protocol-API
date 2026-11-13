<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Map;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Exceptions\HttpResponseException;
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
            // Agente logeado
            $agent = $request->user()->agent;
            $getData = $this->dashboardService->getDashboardData($agent);

            return response()->json($getData, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 422);
        }
    }
}
