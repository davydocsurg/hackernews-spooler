<?php

namespace Tests\Feature\Jobs;

use App\Jobs\FetchStoriesJob;
use App\Services\HackernewsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FetchStoriesJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetch_story_ids_and_data_from_hackernews_service(): void
    {
        // Create an instance of the HackernewsService
        $hackernewsService = app(HackernewsService::class);

        // Call the fetchStoryIds method
        $storyIds = $hackernewsService->fetchStoryIds();

        // Assert that we received an array of story IDs
        $this->assertIsArray($storyIds);

        // Call the fetchStoryData method with a specific story ID
        $storyData = $hackernewsService->fetchStoryData($storyIds[0]);

        // Assert that we received an array of story data
        $this->assertIsArray($storyData);

        // Assert that the story data contains the expected keys
        $this->assertArrayHasKey('by', $storyData);
        $this->assertArrayHasKey('title', $storyData);
        $this->assertArrayHasKey('url', $storyData);
    }

    public function test_fetch_stories_job_dispatched_successfully(): void
    {
        // Ensure the queue is empty
        Queue::fake();

        // Define the expected number of stories to be fetched and processed
        $storyLimit = 10;

        // Mock the HackernewsService
        $hackernewsService = $this->mockHackernewsService();

        // Dispatch the FetchStoriesJob
        FetchStoriesJob::dispatch($hackernewsService, $storyLimit);

        // Assert that the job was pushed to the queue with the correct storyLimit
        Queue::assertPushed(FetchStoriesJob::class, function ($job) use ($storyLimit, $hackernewsService) {
            return $job->storyLimit === $storyLimit && $job->hackernewsService === $hackernewsService;
        });
    }

    /**
     * Mock the HackernewsService
     *
     * @return HackernewsService
     */
    private function mockHackernewsService()
    {
        $mockedHackernewsService = $this->mock(HackernewsService::class);

        return $mockedHackernewsService;
    }
}
