<?php

namespace App\Jobs;

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

class CommentFetcher implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $hackernewsService;
    protected $authorService;

    /**
     * Create a new job instance.
     */
    public function __construct(HackernewsService $hackernewsService, AuthorService $authorService)
    {
        $this->hackernewsService = $hackernewsService;
        $this->authorService = $authorService;
    }

    /**
     * Check if a comment with the given ID already exists in the database.
     *
     * @param int $commentId
     * @return bool
     */
    protected function commentExistsInDatabase(int $commentId): bool
    {
        return Comment::where('comment_id', $commentId)->exists();
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
     * Check if a reply with the given ID already exists in the database.
     *
     * @param int $replyId
     * @return bool
     */
    protected function replyExistsInDatabase(int $replyId): bool
    {
        return Reply::where('reply_id', $replyId)->exists();
    }

    /**
     * Store the fetched comment data in the 'comments' table.
     *
     * @param array $commentData
     * @param Story $story The parent story if it's a top-level comment, or the parent comment if it's a reply
     * @return Comment
     * @throws \Throwable
     */
    protected function storeComment(array $commentData, Story $story): ?Comment
    {
        try {
            // Begin a database transaction
            DB::beginTransaction();

            // Check if the author already exists in the 'authors' table
            $authorId = $this->authorService->getAuthorId($commentData['by']);

            // Create the comment record and associate it with the author, story, and parent comment (if provided)
            $comment = Comment::create([
                'comment_id' => $commentData['id'],
                'text' => $commentData['text'],
                'type' => $commentData['type'],
                // Convert the UNIX timestamp to a DateTime instance
                'time' => \DateTime::createFromFormat('U', $commentData['time']),
                'author_id' => $authorId, // Associate the comment with the author
                'story_id' => $story->id, // Associate the comment with the story
            ]);

            // Commit the transaction
            DB::commit();

            return $comment;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error storing comment data: ' . $th);
            return failedResponse($th, false, 'Error storing comment data');
        }
    }

    /**
     * Fetch and store comments or replies for a story.
     *
     * @param Story $story
     * @param array $commentIds
     * @param Comment|null $parentComment
     */
    public function fetchAndStoreComments(Story $story, array $commentIds, ?Comment $parentComment = null): void
    {
        foreach ($commentIds as $commentId) {
            // Check if the comment or reply already exists in the database to prevent duplicates
            if (!$this->commentExistsInDatabase($commentId)) {
                // Fetch individual comment details
                $commentData = $this->hackernewsService->fetchCommentData($commentId);
                // Validate the fetched comment data (e.g., required fields)
                if ($this->isValidCommentData($commentData)) {
                    // Determine whether it's a comment or a reply based on the presence of a parent comment
                    if ($parentComment) {
                        // Check if the reply already exists in the database to prevent duplicates
                        if (!$this->replyExistsInDatabase($commentData['id'])) {
                            // Store the fetched reply data in the 'replies' table
                            $this->storeReply($commentData, $parentComment);
                        }
                    }

                    // Store the fetched comment data in the 'comments' table
                    $comment = $this->storeComment($commentData, $story);

                    // If this comment or reply has "kids," recursively fetch and store them as comments or replies
                    if (isset($commentData['kids']) && is_array($commentData['kids'])) {
                        if (!$this->replyExistsInDatabase($commentData['id'])) {
                            $this->fetchAndStoreComments($story, $commentData['kids'], $comment);
                        }
                    }
                }
            }
        }
    }

    /**
     * Store the fetched reply data in the 'replies' table.
     *
     * @param array $replyData
     * @param Comment $parentComment
     * @return Reply|Exception
     */
    protected function storeReply(array $replyData, Comment $parentComment): ?Reply
    {
        try {
            // Begin a database transaction
            DB::beginTransaction();

            // Check if the author already exists in the 'authors' table
            $authorId = $this->authorService->getAuthorId($replyData['by']);

            $reply = Reply::create([
                'reply_id' => $replyData['id'],
                'text' => $replyData['text'],
                'type' => $replyData['type'],
                'time' => \DateTime::createFromFormat('U', $replyData['time']),
                'author_id' => $authorId,
                'parent_comment_id' => $parentComment->id, // Associate the reply with its parent comment
            ]);

            // Commit the transaction
            DB::commit();

            return $reply;
        } catch (\Throwable $th) {
            Log::error('Error storing reply data: ' . $th);
            return failedResponse($th, false, 'Error storing reply data');
        }
    }
}
