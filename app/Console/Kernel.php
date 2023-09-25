<?php

namespace App\Console;

use App\Jobs\FetchStoriesJob;
use App\Services\HackernewsService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $hackernewsService = new HackernewsService();

        $schedule->job(new FetchStoriesJob($hackernewsService))->every();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
