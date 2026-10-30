<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbortRequest;
use App\Models\Mission;
use App\Services\AbortService;

class AbortController extends Controller
{
    protected $abortService;

    public function __construct(AbortService $abort)
    {
        $this->abortService = $abort;
    }

    // Solicitar aborto
    public function requestAbort(Mission $mission, AbortRequest $request){
        $event = $this->abortService->requestAbort($request->validated(), $mission);
        
        return response()->json([
            'message' => 'Solicitud de aborto enviada correctamente.',
            'mission' => $event
        ], 200);
    }

    // Aprobar solicitud de aborto
    public function approveAbort(Mission $mission, AbortRequest $request){
        $event = $this->abortService->approveAbort($request->validated(), $mission);
        
        return response()->json([
            'message' => 'Solicitud de aborto aprobada correctamente.',
            'mission' => $event
        ], 200);
    }

    // Rechazar solicitud de aborto
    public function rejectAbort(Mission $mission, AbortRequest $request){
        $event = $this->abortService->rejectAbort($request->validated(), $mission);
        
        return response()->json([
            'message' => 'Solicitud de aborto rechazada correctamente.',
            'mission' => $event
        ], 200);
    }

    // Abortar de forma directa (sin solicitud previa)
    public function abortMissionDirectly(Mission $mission, AbortRequest $request){
        $missionAborted = $this->abortService->abortMissionDirectly($request->validated(), $mission);
        return response()->json($missionAborted['body'], $missionAborted['code']);
    }
}
