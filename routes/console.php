<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:check-mission-deadlines')->everySixHours();
/* Schedule::command('app:abort-expired-missions')->hourly(); */
Schedule::command('app:abort-expired-missions')->dailyAt('16:16');