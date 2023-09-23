<?php

namespace App\Http\Controllers;

use App\Jobs\FetchStoriesJob;
use App\Services\HackernewsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoryController extends Controller
{
    /**
     * Dispatch the job to fetch stories.
     *
     * @param Request $request
     * @return Response
     */
    public function fetchAndStoreStories(HackernewsService $hackernewsService)
    {
        try {
            // $job = new FetchStoriesJob($hackernewsService);
            // $job->dispatch();
            FetchStoriesJob::dispatch($hackernewsService);
            return response()->json([
                'message' => 'Fetching stories job dispatched.',
            ]);
        } catch (\Throwable $e) {
            // Handle any exceptions or errors here
            Log::error('Error dispatching FetchStoriesJob: ' . $e->getMessage());

            return response()->json([
                'error' => 'An error occurred while dispatching the job.',
            ], 500); // Return a 500 Internal Server Error status code
        }
    }
}
