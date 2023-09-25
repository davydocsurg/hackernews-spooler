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
            FetchStoriesJob::dispatch($hackernewsService);
            return successResponse('Fetching stories job dispatched successfully.', true); // Return a 200 OK status code
        } catch (\Throwable $e) {
            // Handle any exceptions or errors here
            Log::error('Error dispatching FetchStoriesJob: ' . $e->getMessage());

            return failedResponse($e, false); // Return a 500 Internal Server Error status code
        }
    }
}
