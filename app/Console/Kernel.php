<?php

namespace App\Console;

use App\Jobs\SendWeeklyExpenseReport;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Run the weekly expense report job every Monday at 9 AM
        $schedule->job(new SendWeeklyExpenseReport)
            ->weeklyOn(1, '09:00'); // 1 = Monday
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
