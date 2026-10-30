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

class NotifyMissionUpdated implements ShouldQueue
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
        // Notificar a agentes que una una misión donde están asignados fue actualizada
        if ($this->agents) {
            $notificationService->notifyMissionUpdate($this->mission, $this->agents);
        }else{
            $notificationService->notifyMissionUpdate($this->mission);
        }
    }
}
