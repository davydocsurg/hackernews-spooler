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
        // Default limit value
        $defaultLimit = config()->get('hackernews.default_story_limit');

        // Get the HackernewsService instance
        $hackernewsService = new HackernewsService();

        // Schedule the FetchStoriesJob to run every 12 hours
        $schedule->job(new FetchStoriesJob($hackernewsService, $defaultLimit))->cron('0 */12 * * *');
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
