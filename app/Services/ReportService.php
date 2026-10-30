<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\MissionEvent;
use App\Models\Report;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // Hacer reporte de misión
    public function newReport(array $data, Mission $mission){
        return DB::transaction(function () use ($data, $mission){
            $user = auth()->user();
            if (!$user->can('leaderAction', $mission)) {
                return [
                    'body' => [
                        'message' => 'Solo el lider de misión puede enviar el reporte.'
                    ], 'code' => 403,
                ];
            }
            
            if ($mission->statement === "waiting_validation") {
                return [
                    'body' => [
                        'message' => 'El reporte de misión ya fue creado.',
                    ],
                    'code' => 409,
                ];
            }
            if ($mission->statement !== "in_progress") {
                return [
                    'body' => [
                        'message' => 'No es posible crear un reporte en el estado actual de la misión.',
                    ],
                    'code' => 409,
                ];
            }

            if ($data['photo'] ?? null) {
                $path = $data['photo']->store('mission', 'public');
            }
            
            Report::create([
                'description' => $data['description'],
                'photo' => $path ?? null,
                'agent_id' => $user->agent->id,
                'mission_id' => $mission->id,
            ]);

            // Actualizar el estado de la misión en la que se hizo el reporte
            $mission->update([
                'statement' => 'waiting_validation'
            ]);

            // Crear evento
            $TYPE_REPORT_SUBMITTED = 'report_submitted';
            MissionEvent::create([
                'agent_id' => $user->agent->id,
                'mission_id' => $mission->id,
                'type' => $TYPE_REPORT_SUBMITTED,
                'description' => 'ha enviado el reporte de misión',
            ]);

            return [
                'body' => [
                    'mission' => $mission->load('agents.class', 'map', 'type', 'leader', 'events.agent', 'report'),
                    'message' => 'Reporte de misión enviado correctamente.',
                ],
                'code' => 200,
            ];
        });
    }

    // Completar la misión
    public function resolveMission(array $data, Mission $mission){
        return DB::transaction(function () use ($data, $mission){
            // Bloquear dobles ejecuciones
            $mission = Mission::where('id', $mission->id)->lockForUpdate()->first();
            // Abortar cambios si ya está completo
            if ($mission->statement === 'completed') {
                return [
                    'body' => [
                        'message' => 'La misión ya fue completada.',
                    ],
                    'code' => 409,
                ];
            }

            // Abortar cambios si ya está completo
            if ($mission->statement !== 'waiting_validation') {
                return [
                    'body' => [
                        'message' => 'No es posible completar una misión en el estado actual.',
                    ],
                    'code' => 409,
                ];
            }

            $user = auth()->user()->load(['agent', 'role']);
            $lastReport = $mission->report()->latest()->first();
            
            if (!$user->can('managementAction', $mission)) {
                return [
                    'body' => [
                        'message' => 'No puedes realizar este cambio.'
                    ], 'code' => 403,
                ];
            }

            // Actualizar estado de misión
            $mission->update([
                'statement' => "completed",
                'result' => $data['result'],
            ]);

            // Habilitar de vuelta a los agentes asignados
            $mission->agents()->update([
                'available' => true,
            ]);

            // Crear evento
            MissionEvent::create([
                'agent_id' => $user->agent->id,
                'mission_id' => $mission->id,
                'type' => "mission_completed",
                'description' => "ha cerrado la misión.",
            ]);

            // Crear respuesta al reporte de misión
            Report::create([
                'description' => $data['description'] ?? null,
                'agent_id' => $user->agent->id,
                'mission_id' => $mission->id,
                'related_report_id' => $lastReport->id ?? null,
            ]);

            return [
                'body' => [
                    'mission' => $mission->load('agents.class', 'map', 'type', 'leader', 'events.agent', 'report'),
                    'message' => 'Misión actualizada correctamente.',
                ],
                'code' => 200,
            ];
        });
    }
}