<?php

namespace App\Console\Commands;

use App\Models\Mission;
use App\Services\AbortService;
use Illuminate\Console\Command;

class AbortExpiredMissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:abort-expired-missions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(AbortService $abortService)
    {
        // Abortar misiones en progreso que hayan superado su fecha limite
        $missions = Mission::where('statement', 'in_progress')->where('deadline', '<', now())->get();
        $abortService->abortExpiredMissions($missions);
    }
}
