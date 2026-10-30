<?php

namespace App\Services;

use App\Jobs\NotifyMissionAssignedAgents;
use App\Jobs\NotifyMissionUnassignedAgents;
use App\Jobs\NotifyMissionUpdated;
use App\Models\Agent;
use App\Models\Mission;
use App\Models\MissionEvent;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MissionService
{
    // Buscar misiones
    public function searchMissions(array $data){
        return DB::transaction(function () use ($data){
            $missions = Mission::with(['agents', 'map', 'type'])
            ->when($data['term'] ?? null, function ($query, $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('objective', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhereHas('map', function ($query) use ($term) {
                            $query->where('name', 'like', "%{$term}%");
                        })
                        ->orWhereHas('agents', function ($query) use ($term) {
                            $query->where('name', 'like', "%{$term}%");
                        });
                });
            })
            ->when($data['type_id'] ?? null, fn ($q, $typeId) => $q->whereIn('type_id', $typeId))
            ->when($data['priority'] ?? null, fn ($q, $priority) => $q->whereIn('priority', $priority))
            ->when($data['statement'] ?? null, fn ($q, $statement) => $q->whereIn('statement', $statement))
            ->when($data['result'] ?? null, fn ($q, $result) => $q->where('statement', 'completed')->whereIn('result', $result))
            ->orderBy('created_at', 'desc')->paginate(20);

            return $missions;
        });
    }

    // Nueva misión
    public function newMission(array $data){
        return DB::transaction(function () use ($data){
            // Prevenir la asignación de agentes no disponibles
            $busyAgents = Agent::whereIn('id', $data['agents'])->where('available', false)->pluck('name');

            if ($busyAgents->isNotEmpty()) {
                throw new HttpResponseException(
                    response()->json([
                        'errors' => [
                            'message' => [
                                'Los siguientes agentes ya están asignados a otras misiones: ' .
                                $busyAgents->join(', ')
                            ]
                        ]
                    ], 422)
                );
            }

            // Crear la misión
            $mission = Mission::create([
                'title' => $data['title'],
                'objective' => $data['objective'],
                'description' => $data['description'],
                'deadline' => $data['deadline'],
                'type_id' => $data['type_id'],
                'priority' => $data['priority'],
                'map_id' => $data['map_id'],
                'leader_id' => $data['leader_id']
            ]);
            
            // Atar agentes a la misión recien creada
            $mission->agents()->attach($data['agents']);

            //Marcar como no disponibles a los agentes asignados
            $mission->agents()->update([
                'available' => false,
            ]);

            // Hacer evento de crear la misión
            $user = auth()->user()->load(['agent']);
            $TYPE_MISSION_UPDATED = 'mission_created';
            $event = MissionEvent::create([
                'agent_id' => $user->agent->id,
                'mission_id' => $mission->id,
                'type' => $TYPE_MISSION_UPDATED,
                'description' => 'ha creado la misión',
            ]);

            // Notificar a los agentes que fueron asignados (en segundo plano)
            NotifyMissionAssignedAgents::dispatch($mission->load('agents'));

            return $mission->load('agents.class', 'map', 'type', 'leader', 'events.agent', 'report');
        });
    }

    // Cambiar el estado de la misión
    public function editMissionStatus(array $data, Mission $mission){

        return DB::transaction(function () use ($data, $mission){
            $user = auth()->user()->load(['agent', 'role']);

            $newStatus = $data['statement'];
            $currentStatus = $mission->statement;

            $eventName = null;
            $eventDescription = null;

            if ($newStatus === 'waiting_start') {
                if (!$user->can('leaderAction', $mission)) {
                    return [
                        'body' => [
                            'message' => 'El usuario no está autorizado para solicitar el inicio de misión.'
                        ], 'code' => 403,
                    ];
                }

                if ($currentStatus !== 'created') {
                    return [
                        'body' => [
                            'message' => 'La misión no se encuentra en un estado que permita solicitar su inicio.'
                        ], 'code' => 409,
                    ];
                }

                $eventName = 'start_check';
                $eventDescription = 'ha solicitado el inicio de la misión';
            } elseif ($newStatus === 'in_progress') {
                if (!$user->can('managementAction', $mission)) {
                    return [
                        'body' => [
                            'message' => 'El usuario no está autorizado para iniciar la misión.'
                        ], 'code' => 403,
                    ];
                }

                if ($currentStatus !== 'waiting_start') {
                    return [
                        'body' => [
                            'message' => 'La misión no se encuentra en un estado que permita iniciarla.'
                        ], 'code' => 409,
                    ];
                }
                
                $eventName = 'mission_started';
                $eventDescription = 'ha iniciado la misión';
            } else {
                return [
                    'body' => ['message' => 'No es posible realizar esta acción.'],
                    'code' => 422,
                ];
            }

            $mission->update([
                'statement' => $data['statement'],
            ]);

            MissionEvent::create([
                'agent_id' => $user->agent->id,
                'mission_id' => $mission->id,
                'type' => $eventName,
                'description' => $eventDescription,
            ]);

            return [
                'body' => [
                    'mission' => $mission->load('agents.class', 'map', 'type', 'leader', 'events.agent', 'report'),
                    'message' => 'Estado de misión actualizado correctamente.',
                ], 'code' => 200,
            ];
        });
    }

    // Actualizar detalles de misión
    public function updateMissionDetails(array $data, Mission $mission){
        return DB::transaction(function () use ($data, $mission){
            $user = auth()->user();
            if (!$user->can('managementAction', $mission)) {
                return [
                    'body' => [
                        'message' => 'No puedes editar una misión de la que eres lider.'
                    ], 'code' => 403,
                ];
            }

            $allowedStatus = ['created', 'waiting_start'];
            if (!in_array($mission->statement, $allowedStatus)) {
                return [
                    'body' => [
                        'message' => 'No puedes editar una misión en este estado.'
                    ],
                    'code' => 403,
                ];
            }

            $mission->update([
                'title' => $data['title'],
                'objective' => $data['objective'],
                'description' => $data['description'],
                'deadline' => $data['deadline'],
                'type_id' => $data['type_id'],
                'priority' => $data['priority'],
                'map_id' => $data['map_id'],
            ]);

            $user = auth()->user()->load(['agent']);
            // Crear en la migración un evento mission_updated
            $TYPE_MISSION_UPDATED = 'mission_updated';
            MissionEvent::create([
                'agent_id' => $user->agent->id,
                'mission_id' => $mission->id,
                'type' => $TYPE_MISSION_UPDATED,
                'description' => 'ha actualizado los detalles de la misión',
            ]);

            // Notificar actualización a los agentes asignados (en segundo plano)
            NotifyMissionUpdated::dispatch($mission->load('agents'));

            return [
                'body' => [
                    'mission' => $mission->load('agents.class', 'map', 'type', 'leader', 'events.agent', 'report'),
                    'message' => 'Misión actualizada correctamente.',
                ],
                'code' => 201,
            ];
        });
    }

    // Reasignar agentes de la misión
    public function updateMissionAgents(array $data, Mission $mission){
        return DB::transaction(function () use ($data, $mission){
            $user = auth()->user();
            if (!$user->can('managementAction', $mission)) {
                return [
                    'body' => [
                        'message' => 'No puedes editar una misión de la que eres lider.'
                    ], 'code' => 403,
                ];
            }

            $allowedStatus = ['created', 'waiting_start'];
            if (!in_array($mission->statement, $allowedStatus)) {
                return [
                    'body' => [
                        'message' => 'No puedes editar una misión en este estado.'
                    ],
                    'code' => 403,
                ];
            }

            $mission->update([
                'leader_id' => $data['leader_id']
            ]);

            $currentAgents = $mission->agents()->pluck('agents.id')->toArray();
            $newAgents = $data['agents'];

            // Nuevos agentes agregados
            $addedAgents = array_diff($newAgents, $currentAgents);
            // Agentes removidos
            $removedAgents = array_diff($currentAgents, $newAgents);

            // Verificar si hay o no agentes no disponibles entre los nuevos agentes
            $busyAgents = Agent::whereIn('id', $addedAgents)->where('available', false)->pluck('name'); // o name

            if ($busyAgents->isNotEmpty()) {
                return [
                    'body' => [
                        'message' => [
                            'Los siguientes agentes ya están asignados a otras misiones: ' .
                                $busyAgents->join(', ')
                        ],
                    ],
                    'code' => 422,
                ];
            }

            // Actualizar relaciones
            $mission->agents()->sync($newAgents);

            // Marcar como no disponibles a los nuevos agentes
            Agent::whereIn('id', $addedAgents)->update([
                'available' => false,
            ]);
            // Los removidos vuelven a estar marcados como disponibles
            Agent::whereIn('id', $removedAgents)->update([
                'available' => true,
            ]);

            $user = auth()->user()->load(['agent']);
            // Crear en la migración un evento mission_updated
            $TYPE_MISSION_UPDATED = 'mission_updated';
            MissionEvent::create([
                'agent_id' => $user->agent->id,
                'mission_id' => $mission->id,
                'type' => $TYPE_MISSION_UPDATED,
                'description' => 'ha actualizado los agentes asignados a la misión',
            ]);

            // Notificar a los nuevos agentes que fueron asignados (en segundo plano)
            NotifyMissionAssignedAgents::dispatch($mission->load('agents'), $addedAgents);
            // Notificar a los agentes que fueron desasignados (en segundo plano)
            NotifyMissionUnassignedAgents::dispatch($mission->load('agents'), $removedAgents);
            // Agentes que se mantienen (para crear la notificación)
            $remainingAgents = array_intersect($currentAgents, $newAgents);
            // Notificar de la actualización a los agentes que persistieron (en segundo plano)
            NotifyMissionUpdated::dispatch($mission->load('agents'), $remainingAgents);

            return [
                'body' => [
                    'mission' => $mission->load('agents.class', 'map', 'type', 'leader', 'events.agent', 'report'),
                    'message' => 'Misión actualizada correctamente.',
                ],
                'code' => 201,
            ];
        });
    }
}