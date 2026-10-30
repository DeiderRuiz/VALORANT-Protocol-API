<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationAgent;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    // Obtener todas las notificaciones del agente logeado
    public function getNotifications(){
        $agent = auth()->user()->agent;

        $notifications = $agent->notifications()->with('mission')->latest()->limit(20)->get();
        
        return response()->json($notifications, 200);
    }

    // Marcar como leida unanotificación
    public function readNotification(NotificationAgent $notificationPivot){
        $notification = $this->notificationService->readNotification($notificationPivot);
        return $notification;
    }
}
