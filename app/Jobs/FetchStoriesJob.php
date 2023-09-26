<?php

namespace App\Jobs;

use App\Services\HackernewsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchStoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $hackernewsService;
    public $storyLimit;

    /**
     * Create a new job instance.
     *
     * @param HackernewsService $hackernewsService
     */
    public function __construct(HackernewsService $hackernewsService, int $storyLimit)
    {
        $this->hackernewsService = $hackernewsService;
        $this->storyLimit = $storyLimit;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Fetch and store stories
        (new StoryFetcher($this->hackernewsService, $this->storyLimit))->fetchAndStoreStories();
    }
}
