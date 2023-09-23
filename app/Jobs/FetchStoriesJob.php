<?php

namespace App\Jobs;

use App\Models\Story;
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
        // Fetch story IDs from the Hackernews API
        $storyIds = $this->hackernewsService->fetchStoryIds();

        foreach ($storyIds as $storyId) {
            // Check if the story already exists in the database to prevent duplicates
            if (!$this->storyExists($storyId)) {
                // Fetch individual story details
                $storyData = $this->hackernewsService->fetchStoryData($storyId);

                // Validate the fetched story data before storing it in the database
                if ($this->isValidStoryData($storyData)) {
                    // Store the fetched story data in the 'stories' table
                    $this->storeStoryData($storyData);
                }
            }
        }
    }

    /**
     * Check if a story with the given ID already exists in the database.
     *
     * @param int $storyId
     * @return bool
     */
    protected function storyExists(int $storyId): bool
    {
        return Story::where('story_id', $storyId)->exists();
    }

    /**
     * Validate the fetched story data.
     * Add your validation rules here.
     *
     * @param array $storyData
     * @return bool
     */
    protected function isValidStoryData(array $storyData): bool
    {
        // Check if 'title' and 'url' are present
        return isset($storyData['title']) && isset($storyData['url']);
    }

    /**
     * Store the fetched story data in the 'stories' table.
     *
     * @param array $storyData
     */
    protected function storeStoryData(array $storyData): void
    {
        Story::create([
            'story_id' => $storyData['id'],
            'title' => $storyData['title'],
            'url' => $storyData['url'],
            'type' => $storyData['type'],
            'score' => $storyData['score'],
            'time' => $storyData['time'],
            'author_id' => $storyData['by'],
            'descendants' => $storyData['descendants'],
        ]);
    }
}
