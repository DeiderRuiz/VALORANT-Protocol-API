<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\Notification;
use App\Models\NotificationAgent;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function readNotification(NotificationAgent $notificationPivot) {
        $agent = auth()->user()->agent;
        $notificationAgentId = $notificationPivot->agent_id;
        if ($agent->id !== $notificationAgentId) {
            return response()->json([
                'message' => 'No tienes autorización para modificar esta notificación.'
            ], 403);
        }
        // Actualiza el pivote entre la notificación recibida y el agente logeado
        $notificationPivot->update([
            'read' => true,
            'read_at' => now(),
        ]);

        return response()->noContent();
    }

    // Para notificar a agentes que fueron asignados a una misión
    // agents es para el caso de reasignación de agentes, cuando agents 
    // es null se notifica a todos los involucrados en la misión
    public function notifyMissionAssigned(Mission $mission, ?array $agents = null){
        $agents ??= $mission->agents->pluck('id')->toArray();

        $notification = Notification::create([
            'mission_id' => $mission->id,
            'details' => "Has sido asignado a la misión",
            'type' => "mission_assigned"
        ]);

        // Atar a cada agente asignado a la misión a la notificación recién creada
        $notification->agents()->attach($agents);

        return $notification;
    }

    // Para notificar a agentes que fueron desasignados de una misión
    public function notifyMissionUnassigned(Mission $mission, array $agents){
        $notification = Notification::create([
            'mission_id' => $mission->id,
            'details' => "Has sido desasignado de la misión",
            'type' => "mission_unassigned"
        ]);

        // Atar a cada agente asignado a la misión a la notificación recién creada
        $notification->agents()->attach($agents);

        return $notification;
    }

    // Para notificar a agentes que hubo una actualización
    // agents es para el caso de reasignación de agentes, 
    // notifica a agentes que permanecen tras una reasignación
    public function notifyMissionUpdate(Mission $mission, ?array $agents = null){
        $agents ??= $mission->agents->pluck('id')->toArray();
        
        $notification = Notification::create([
            'mission_id' => $mission->id,
            'details' => "Se ha actualizado la misión",
            'type' => "mission_updated"
        ]);

        // Atar a cada agente asignado a la misión a la notificación recién creada
        $notification->agents()->attach($agents);

        return $notification;
    }

    public function checkDeadlines(Collection $missions) {
        // Crear notificaciones de aviso a cada misión
        foreach ($missions as $mission) {
            switch ($mission->statement) {
                case 'created':
                    // NOTIFICAR A LIDER, ADMIN Y ASSISTANTS
                    $message = "La misión se acerca a su fecha límite y aún no ha comenzado";
                    $type = "mission_created_deadline_warning";
                    $this->notifyNearDeadline($mission, $message, $type);
                    break;
    
                case 'waiting_start':
                    // NOTIFICAR A ADMIN Y ASSISTANTS
                    $message = "La misión se acerca a su fecha límite y aún está esperando para comenzar.";
                    $type = "mission_waiting_start_deadline_warning";
                    $this->notifyNearDeadline($mission, $message, $type);
                    break;

                case 'waiting_validation':
                    // NOTIFICAR A ADMIN Y ASSISTANTS
                    $message = "La misión se acerca a su fecha límite y está esperando validación.";
                    $type = "mission_validation_warning";
                    $this->notifyNearDeadline($mission, $message, $type);
                    break;
            }
        }
    }

    private function notifyNearDeadline(Mission $mission, string $message, string $type) {
        $lastNotification = $mission->notifications()->latest()->first();
        if ($lastNotification && $lastNotification->type === $type) {
            return;
        }
        
        $admins = User::with(['agent', 'role'])
            ->whereHas('role', function ($query) {
                $query->whereIn('name', ['admin', 'assistant']);
            })->get();

        $agents = $admins->pluck('agent.id')->filter()->toArray();

        // Agergar al lider de misión en el array de agentes si la misión está en estado created
        if ($mission->statement === 'created') {
            $agents[] = $mission->leader_id;
        }

        $notification = Notification::create([
            'mission_id' => $mission->id,
            'details' => $message,
            'type' => $type
        ]);

        // Atar a cada agente asignado a la misión a la notificación recién creada
        $notification->agents()->attach($agents);
    }
}