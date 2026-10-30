<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Http\Requests\ResolveMissionRequest;
use App\Models\Mission;
use App\Services\ReportService;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    // Nuevo reporte de misión (Solo el lider lo puede hacer)
    public function newReport(ReportRequest $request, Mission $mission){
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo');
        }

        $missionUpdated = $this->reportService->newReport($data, $mission);

        return response()->json($missionUpdated['body'], $missionUpdated['code']);
    }

    // Cierre de la misión (marcar como completa y darle resultado de exito o frcaso)
    public function resolveMission(ResolveMissionRequest $request, Mission $mission){
        $missionUpdated = $this->reportService->resolveMission($request->validated(), $mission);
        return response()->json($missionUpdated['body'], $missionUpdated['code']);
    }
}
