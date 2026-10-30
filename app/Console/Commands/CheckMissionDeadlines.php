<?php

namespace App\Console\Commands;

use App\Models\Mission;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckMissionDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-mission-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $statements = [
            'created',
            'waiting_start',
            'waiting_validation',
        ];

        /* Obtener las misiones que se encuentren en los estados declarados y 
        que ya hayan transcurrido la mitad del tiempo o más entre la fecha 
        de creación y la fecha limite */
        $missions = Mission::whereIn('statement', $statements)
        ->whereRaw(
            'NOW() >= created_at + INTERVAL TIMESTAMPDIFF(SECOND, created_at, deadline) / 2 SECOND'
        )->get();

        $notificationService->checkDeadlines($missions);
    }
}
