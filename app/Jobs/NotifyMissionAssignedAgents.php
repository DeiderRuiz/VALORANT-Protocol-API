<?php

namespace App\Jobs;

use App\Models\Mission;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyMissionAssignedAgents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Mission $mission, 
        public ? array $agents = null
    ){}

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        // Notificar a agentes que fueron asignados a una misión
        if ($this->agents) {
            $notificationService->notifyMissionAssigned($this->mission, $this->agents);
        }else{
            $notificationService->notifyMissionAssigned($this->mission);
        }
    }
}
