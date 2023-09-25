<?php

namespace App\Jobs;

use App\Models\Author;
use App\Models\Comment;
use App\Models\Reply;
use App\Models\Story;
use App\Services\HackernewsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
                    $story = $this->storeStoryData($storyData);

                    // Fetch and store comments for this story
                    $this->fetchAndStoreComments($story, $storyData['kids']);
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
     * @param int $commentId
     * @return bool
     */
    protected function commentExists(int $commentId): bool
    {
        return Comment::where('comment_id', $commentId)->exists();
    }

    /**
     * Check if a reply with the given ID already exists in the database.
     *
     * @param int $replyId
     * @return bool
     */
    protected function replyExists(int $replyId): bool
    {
        return Reply::where('reply_id', $replyId)->exists();
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
     * Validate the fetched comment data.
     * Add your validation rules here.
     *
     * @param array $commentData
     * @return bool
     */
    protected function isValidCommentData(array $commentData): bool
    {
        // Check if 'text' are present
        return isset($commentData['text']);
    }

    /**
     * Create or retrieve an author based on the username.
     *
     * @param string $username
     * @return Author
     */
    protected function getOrCreateAuthor(string $username): Author
    {
        $author = Author::firstOrNew(['username' => $username], ['username' => $username]);
        if (!$author->exists) {
            // If the author is new, save the author record
            $author->save();
        }

        return $author;
    }

    /**
     * Store the fetched story data in the 'stories' table.
     *
     * @param array $storyData
     */
    protected function storeStoryData(array $storyData): Story
    {
        try {
            // Check if the author already exists in the 'authors' table
            $author = $this->getOrCreateAuthor($storyData['by']);

            // Create the story record and associate it with the author
            $story =    Story::create([
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

            return $story;
        } catch (\Throwable $th) {
            Log::error('Error storing story data: ' . $th);
            return failedResponse($th, false, 'Error storing story data');
        }
    }

    /**
     * Fetch and store comments or replies for a story.
     *
     * @param Story $story
     * @param array $commentIds
     * @param Comment|null $parentComment
     */
    protected function fetchAndStoreComments(Story $story, array $commentIds, ?Comment $parentComment = null): void
    {
        foreach ($commentIds as $commentId) {
            // Check if the comment or reply already exists in the database to prevent duplicates
            if (!$this->commentExists($commentId)) {
                // Fetch individual comment details
                $commentData = $this->hackernewsService->fetchCommentData($commentId);
                // Validate the fetched comment data (e.g., required fields)
                if ($this->isValidCommentData($commentData)) {
                    // Determine whether it's a comment or a reply based on the presence of a parent comment
                    if ($parentComment) {
                        // Store the fetched reply data in the 'replies' table
                        $this->storeReplyData($commentData, $parentComment);
                    }

                    // Store the fetched comment data in the 'comments' table
                    $comment = $this->storeCommentData($commentData, $story);

                    // If this comment or reply has "kids," recursively fetch and store them as comments or replies
                    if (isset($commentData['kids']) && is_array($commentData['kids'])) {
                        $this->fetchAndStoreComments($story, $commentData['kids'], $comment);
                    }
                }
            }
        }
    }

    /**
     * Store the fetched comment data in the 'comments' table.
     *
     * @param array $commentData
     * @param Story $story The parent story if it's a top-level comment, or the parent comment if it's a reply
     * @return Comment
     */
    protected function storeCommentData(array $commentData, Story $story): Comment
    {
        try {
            // Check if the author already exists in the 'authors' table
            $author = $this->getOrCreateAuthor($commentData['by']);

            // Create the comment record and associate it with the author, story, and parent comment (if provided)
            $comment = Comment::create([
                'comment_id' => $commentData['id'],
                'text' => $commentData['text'],
                'type' => $commentData['type'],
                // Convert the UNIX timestamp to a DateTime instance
                'time' => \DateTime::createFromFormat('U', $commentData['time']),
                'author_id' => $author->id, // Associate the comment with the author
                'story_id' => $story->id, // Associate the comment with the story
            ]);

            return $comment;
        } catch (\Throwable $th) {
            Log::error('Error storing comment data: ' . $th);
            return failedResponse($th, false, 'Error storing comment data');
        }
    }

    /**
     * Store the fetched reply data in the 'replies' table.
     *
     * @param array $replyData
     * @param Comment $parentComment
     * @return Reply
     */
    protected function storeReplyData(array $replyData, Comment $parentComment): Reply
    {
        try {
            // Check if the author already exists in the 'authors' table
            $author = $this->getOrCreateAuthor($replyData['by']);

            $reply = Reply::create([
                'reply_id' => $replyData['id'],
                'text' => $replyData['text'],
                'type' => $replyData['type'],
                'time' => \DateTime::createFromFormat('U', $replyData['time']),
                'author_id' => $author->id,
                'parent_comment_id' => $parentComment->id, // Associate the reply with its parent comment
            ]);

            return $reply;
        } catch (\Throwable $th) {
            Log::error('Error storing reply data: ' . $th);
            return failedResponse($th, false, 'Error storing reply data');
        }
    }
}
