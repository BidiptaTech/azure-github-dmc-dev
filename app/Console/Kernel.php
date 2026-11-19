<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('update:tour-status')
             ->dailyAt('23:59'); // end of day

        // Online payment holds: settle finished tours, force-settle before Stripe holds expire.
        $schedule->command('payments:settle-online-holds')
             ->everyFifteenMinutes()
             ->withoutOverlapping();

        // Month/year partitions: keep the next months ready.
        $schedule->command('partitions:ensure')
             ->monthlyOn(1, '00:30')
             ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
