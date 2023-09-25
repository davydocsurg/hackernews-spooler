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

class StoryFetcher implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $hackernewsService;
    protected $authorService;

    /**
     * Create a new job instance.
     */
    public function __construct(HackernewsService $hackernewsService)
    {
        $this->hackernewsService = $hackernewsService;
        $authorService = new AuthorService();
        $this->authorService = $authorService;
    }

    /**
     * Check if a story with the given ID already exists in the database.
     *
     * @param int $storyId
     * @return bool
     */
    protected function storyExistsInDatabase(int $storyId): bool
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
    protected function storeStory(array $storyData): Story
    {
        try {
            // Check if the author already exists in the 'authors' table
            $authorId = $this->authorService->getAuthorId($storyData['by']);

            // Create the story record and associate it with the author
            $story = Story::create([
                'story_id' => $storyData['id'],
                'title' => $storyData['title'],
                'url' => $storyData['url'],
                'type' => $storyData['type'],
                'score' => $storyData['score'],
                // Convert the UNIX timestamp to a DateTime instance
                'time' => \DateTime::createFromFormat('U', $storyData['time']),
                'author_id' => $authorId, // Associate the story with the author
                'descendants' => $storyData['descendants'],
            ]);

            return $story;
        } catch (\Throwable $th) {
            Log::error('Error storing story data: ' . $th);
            return failedResponse($th, false, 'Error storing story data');
        }
    }

    /**
     * Execute the job.
     */
    public function fetchAndStoreStories(): void
    {
        // Fetch story IDs from the Hackernews API
        $storyIds = $this->hackernewsService->fetchStoryIds();

        // Limit the number of stories to 10
        $storyIds = array_slice($storyIds, 0, 10);

        foreach ($storyIds as $storyId) {
            // Check if the story already exists in the database to prevent duplicates
            if (!$this->storyExistsInDatabase($storyId)) {
                // Fetch individual story details
                $storyData = $this->hackernewsService->fetchStoryData($storyId);

                // Validate the fetched story data before storing it in the database
                if ($this->isValidStoryData($storyData)) {
                    // Store the fetched story data in the 'stories' table
                    $story = $this->storeStory($storyData);

                    // Fetch and store comments for this story
                    (new CommentFetcher($this->hackernewsService, $this->authorService))->fetchAndStoreComments($story, $storyData['kids']);
                }
            }
        }
    }
}
