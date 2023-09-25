<?php

namespace App\Console\Commands;

use App\Jobs\FetchStoriesJob;
use App\Services\HackernewsService;
use Illuminate\Console\Command;

class FetchStoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spool:fetch-stories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spool data from the Hackernews API and store it in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hackernewsService = new HackernewsService();
        FetchStoriesJob::dispatch($hackernewsService);
    }
}
