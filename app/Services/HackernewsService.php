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
        $params = 'orderBy=%22$priority%22&limitToFirst=100';
        $response = Http::get($storiesEndpoint . '.json?' . $printParams . $params);

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
    public function fetchStory(int $storyId)
    {
        $storyEndpoint = config()->get('hackernews.story_endpoint');
        $printParams = 'print=pretty';
        $response = Http::get($storyEndpoint . $storyId . '.json?' . $printParams);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }
}
