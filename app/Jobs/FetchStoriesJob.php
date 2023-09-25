<?php

namespace App\Jobs;

use App\Models\Author;
use App\Models\Comment;
use App\Models\Reply;
use App\Models\Story;
use App\Services\AuthorService;
use App\Services\HackernewsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FetchStoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $hackernewsService;

    /**
     * Create a new job instance.
     *
     * @param HackernewsService $hackernewsService
     */
    public function __construct(HackernewsService $hackernewsService)
    {
        $this->hackernewsService = $hackernewsService;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // DB::transaction(function () {
        // Fetch and store stories
        (new StoryFetcher($this->hackernewsService))->fetchAndStoreStories();
        // });
    }
}
