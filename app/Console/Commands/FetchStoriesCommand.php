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
    protected $signature = 'spool:fetch-stories { --limit=100: The number of stories to fetch }';

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
        // Fetch the story limit from the command line
        $storyLimit = $this->option('limit');

        // Get the HackernewsService instance
        $hackernewsService = new HackernewsService();

        // Display a message indicating that fetching and processing is in progress
        $this->output->write("<fg=white>Fetching and processing {$storyLimit} " . ($storyLimit > 1 ? 'stories' : 'story') . '...</>');
        $this->output->newLine();

        // Dispatch the FetchStories job
        FetchStoriesJob::dispatch($hackernewsService, $storyLimit);

        // Display a completion message
        $this->info("Fetched and processed {$storyLimit} " . ($storyLimit > 1 ? 'stories' : 'story') . ' successfully.');
    }
}
