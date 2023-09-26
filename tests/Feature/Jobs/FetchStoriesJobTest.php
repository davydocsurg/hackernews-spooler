<?php

namespace Tests\Feature\Jobs;

use App\Jobs\FetchStoriesJob;
use App\Services\HackernewsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

    /**
     * Test that the FetchStoriesJob is dispatched and processed correctly.
     *
     * @return void
     */
    // public function test_fetch_stories_job_dispatched_and_processed_correctly(): void
    // {
    //     // Mock the HackernewsService to prevent actual API calls during testing
    //     $hackernewsService = $this->mock(HackernewsService::class);

    //     // Define the expected number of stories to be fetched and processed
    //     $storyLimit = 10;

    //     // Mock the expected behavior of the HackernewsService
    //     $hackernewsService->shouldReceive('fetchStoryIds')->andReturn([1, 2, 3]); // Example story IDs

    //     // Dispatch the FetchStoriesJob
    //     FetchStoriesJob::dispatch($hackernewsService, $storyLimit);

    //     // Assert that the job was pushed to the queue
    //     Queue::assertPushed(FetchStoriesJob::class);

    //     // Optionally, you can assert that specific methods are called within the job's handle method
    //     $hackernewsService->shouldReceive('fetchStoryData')->times($storyLimit)->andReturn([
    //         'by' => 'test',
    //         'descendants' => 1,
    //         'id' => 1,
    //         'kids' => [1, 2, 3],
    //         'score' => 1,
    //         'time' => 1,
    //         'title' => 'test',
    //         'type' => 'test',
    //         'url' => 'test'
    //     ]);

    //     // Run the job
    //     Queue::assertPushed(FetchStoriesJob::class, function ($job) use ($storyLimit) {
    //         $job->handle();
    //         return true;
    //     });
    // }
}
