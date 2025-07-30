<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot(Schedule $schedule): void
    {
        $schedule->command('app:check-delete-counters')->hourly();
        $schedule->command('app:change-trip-status')->hourly();
        $schedule->command('app:change-status-event-volunteering')->daily();
        $schedule->command('app:change-guide-trip-status')->hourly();
    }
}
