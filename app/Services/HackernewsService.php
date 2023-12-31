<?php

namespace App\Services;

use App\Models\Author;
use Illuminate\Support\Facades\Http;

class HackernewsService
{
    /**
     * Fetches the author from the Hackernews API.
     *
     * @return array<string, mixed>
     */
    public function fetchStoryIds()
    {
        $storiesEndpoint = config()->get('hackernews.stories_endpoint');
        $printParams = 'print=pretty';
        $params = '&orderBy=%22$priority%22&limitToFirst=19';
        $response = Http::get($storiesEndpoint . '.json?' . $printParams);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Fetch a story from the Hackernews API.
     *
     * @param int $storyId
     * @return array<string, mixed>
     */
    public function fetchStoryData(int $storyId)
    {
        $storyEndpoint = config()->get('hackernews.story_endpoint');
        $printParams = 'print=pretty';
        $response = Http::get($storyEndpoint . $storyId . '.json?' . $printParams);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Fetch a comment from the Hackernews API.
     *
     * @param int $commentId
     * @return array<string, mixed>
     */
    public function fetchCommentData(int $commentId)
    {
        $commentEndpoint = config()->get('hackernews.comment_endpoint');
        $printParams = 'print=pretty';
        $response = Http::get($commentEndpoint . $commentId . '.json?' . $printParams);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }
}
