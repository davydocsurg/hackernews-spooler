<?php

namespace App\Jobs;

use App\Models\Author;
use App\Models\Comment;
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
     * Check if a comment with the given ID already exists in the database.
     *
     * @param int $storyId
     * @return bool
     */
    protected function commentExists(int $commentId): bool
    {
        return Comment::where('comment_id', $commentId)->exists();
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
        // Check if the author already exists in the 'authors' table
        $author = Author::firstOrNew(['username' => $storyData['by']]);

        if (!$author->exists) {
            // If the author is new, save the author record
            $author->save();
        }

        // Create the story record and associate it with the author
        Story::create([
            'story_id' => $storyData['id'],
            'title' => $storyData['title'],
            'url' => $storyData['url'],
            'type' => $storyData['type'],
            'score' => $storyData['score'],
            // Convert the UNIX timestamp to a DateTime instance
            'time' => \DateTime::createFromFormat('U', $storyData['time']),
            'author_id' => $author->id, // Associate the story with the author
            'descendants' => $storyData['descendants'],
        ]);
    }

    /**
     * Fetch and store comments for a story.
     *
     * @param Story $story
     * @param array $commentIds
     */
    // protected function fetchAndStoreComments(Story $story, array $commentIds): void
    // {
    //     foreach ($commentIds as $commentId) {
    //         // Check if the comment already exists in the database to prevent duplicates
    //         if (!$this->commentExists($commentId)) {
    //             // Fetch individual comment details
    //             $commentData = $this->hackernewsService->fetchCommentData($commentId);

    //             // Validate the fetched comment data (e.g., required fields)
    //             if ($this->isValidCommentData($commentData)) {
    //                 // Store the fetched comment data in the 'comments' table
    //                 $comment = $this->storeCommentData($commentData);

    //                 // Associate the comment with the story
    //                 $story->comments()->save($comment);

    //                 // Fetch and store the author for this comment
    //                 $this->fetchAndStoreAuthor($commentData['by']);
    //             }
    //         }
    //     }
    // }
}
