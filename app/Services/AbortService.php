<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\MissionEvent;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AbortService
{
    public function requestAbort(array $data, Mission $mission){
        $user = auth()->user()->load(['agent']);

        $lastAbortRequest = $mission->events()
            ->whereIn('type', ['abort_request', 'abort_approved', 'abort_rejected'])
            ->latest()->first();

        if (!$user->can('leaderAction', $mission)) {
            return [
                'body' => [
                    'message' => 'No tienes permisos para realizar esta acción.'
                ], 'code' => 403,
            ];
        }

        if (!$lastAbortRequest && $mission->statement === "created") {
            MissionEvent::create([
                'agent_id' => $user->agent->id,
                'mission_id' => $mission->id,
                'type' => 'abort_request',
                'description' => "ha solicitado abortar la misión",
                'details' => $data['details'],
            ]);
        } else {
            return [
                'body' => [
                    'message' => 'No es posible realizar esta acción.'
                ], 'code' => 409,
            ];
        }

        return $mission->load('agents.class', 'map', 'type', 'leader', 'events.agent', 'report');
    }

    public function rejectAbort(array $data, Mission $mission){
        return DB::transaction(function () use ($data, $mission){
            $user = auth()->user()->load(['agent', 'role']);

            $lastAbortRequest = $mission->events()
                ->whereIn('type', ['abort_request', 'abort_approved', 'abort_rejected'])
                ->latest()->first();

            if (!$user->can('managementAction', $mission)) {
                return [
                    'body' => [
                        'message' => 'No tienes permisos para realizar esta acción.'
                    ], 'code' => 403,
                ];
            }

            if ($lastAbortRequest->type == "abort_request" && $mission->statement === "created") {
                $event = MissionEvent::create([
                    'agent_id' => $user->agent->id,
                    'mission_id' => $mission->id,
                    'type' => 'abort_rejected',
                    'description' => "ha rechazado la solicitud de aborto de la misión",
                    'details' => $data['details'],
                    'related_event_id' => $lastAbortRequest->id ?? null
                ]);

                return $mission->load('agents.class', 'map', 'type', 'leader', 'events.agent', 'report');
            } else {
                return [
                    'body' => [
                        'message' => 'No es posible realizar esta acción.'
                    ], 'code' => 409,
                ];
            }
        });
    }

    public function approveAbort(array $data, Mission $mission){
        $user = auth()->user()->load(['agent', 'role']);

        $lastAbortRequest = $mission->events()
            ->whereIn('type', ['abort_request', 'abort_approved', 'abort_rejected'])
            ->latest()->first();

        if (!$user->can('managementAction', $mission)) {
            return [
                'body' => [
                    'message' => 'No tienes permisos para realizar esta acción.'
                ], 'code' => 403,
            ];
        }
        
        if ($lastAbortRequest->type == "abort_request" && $mission->statement === "created") {
            $eventData = [
                'type' => 'abort',
                'details' => $data['details'],
                'description' => 'ha aprobado la solicitud de aborto de la misión',
            ];
            return $this->abortMission($eventData, $mission, $user);
        } else {
            return [
                'body' => ['message' => 'No es posible realizar esta acción.'],
                'code' => 409,
            ];
        }
    }

    public function abortMissionDirectly(array $data, Mission $mission){
        $user = auth()->user()->load(['agent', 'role']);
        $currentStatus = $mission->statement;

        if ($user->can('leaderAction', $mission)) {
            if ($currentStatus  !== 'in_progress') {
                return [
                    'body' => [
                        'message' => 'La misión no se encuentra en un estado que permita abortarla.'
                    ], 'code' => 409,
                ];
            }
        } else if ($user->can('managementAction', $mission)) {
            if ($currentStatus !== 'created' && $currentStatus !== 'waiting_start') {
                return [
                    'body' => [
                        'message' => 'La misión no se encuentra en un estado que permita abortarla.'
                    ], 'code' => 409,
                ];
            }
        } else {
            return [
                'body' => [
                    'message' => 'No tienes permisos para realizar esta acción.'
                ], 'code' => 403,
            ];
        }

        $eventData = [
            'type' => 'abort',
            'details' => $data['details'],
            'description' => 'ha abortado la misión',
        ];
        return $this->abortMission($eventData, $mission, $user);
    }

    private function abortMission(array $eventData, Mission $mission, User $user){
        return DB::transaction(function () use ($eventData, $mission, $user){
            MissionEvent::create([
                'agent_id' => $user->agent->id,
                'mission_id' => $mission->id,
                'type' => $eventData['type'],
                'description' => $eventData['description'],
                'details' => $eventData['details'],
                'related_event_id' => $lastAbortRequest->id ?? null
            ]);

            $mission->update([
                'statement' => 'aborted',
            ]);

            if ($mission->statement === 'aborted') {
                $mission->agents()->update([
                    'available' => true,
                ]);
            }

            return [
                'body' => [
                    'mission' => $mission->load('agents.class', 'map', 'type', 'leader', 'events.agent', 'report'),
                    'message' => 'Estado de misión actualizado correctamente.',
                ],
                'code' => 200,
            ];
        });
    }

    public function abortExpiredMissions(Collection $missions) {
        DB::transaction(function () use ($missions){
            // Abortar todas las misiones en estado in_progress que hayan superado su fecha limite
            foreach ($missions as $mission) {
                MissionEvent::create([
                    'agent_id' => null,
                    'mission_id' => $mission->id,
                    'type' => 'abort',
                    'description' => 'ha abortado la misión',
                    'details' => 'El plazo para cumplir la misión ha vencido',
                ]);

                $mission->update([
                    'statement' => 'aborted',
                ]);

                // Habilitar a lso agentes que estuvieron en la misión
                if ($mission->statement === 'aborted') {
                    $mission->agents()->update([
                        'available' => true,
                    ]);
                }

                $admins = User::with(['agent', 'role'])
                    ->whereHas('role', function ($query) {
                        $query->whereIn('name', ['admin', 'assistant']);
                    })->get();
        
                /* Array con los ids de los agentes asignados a la misión 
                más los agentes con roles admin y assistant */
                $agents = $admins->pluck('agent.id')->filter()
                    ->merge($mission->agents->pluck('id'))->unique()
                    ->values()->toArray();

                $notification = Notification::create([
                    'mission_id' => $mission->id,
                    'details' => 'El plazo para cumplir la misión ha vencido',
                    'type' => 'mission_aborted'
                ]);

                // Atar a cada agente asignado a la misión más admin y assistants a la notificación recién creada
                $notification->agents()->attach($agents);
            }
        });
    }
}