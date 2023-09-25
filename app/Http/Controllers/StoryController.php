<?php

namespace App\Http\Controllers;

use App\Jobs\FetchStoriesJob;
use App\Services\HackernewsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StoryController extends Controller
{
    /**
     * Dispatch the job to fetch stories.
     *
     * @param HackernewsService $hackernewsService
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAndStoreStories(HackernewsService $hackernewsService, Request $request)
    {
        try {
            $validated = $this->validateRequest($request);
            if ($validated->fails()) {
                return otherError(400, false, $validated->errors()); // Return a 400 Bad Request status code
            }

            // FetchStoriesJob::dispatch($hackernewsService, $request->limit);
            dispatch(new FetchStoriesJob($hackernewsService, $request->limit));

            return successResponse('Fetching stories job dispatched successfully.', true); // Return a 200 OK status code
        } catch (\Throwable $e) {
            // Handle any exceptions or errors here
            Log::error('Error dispatching FetchStoriesJob: ' . $e->getMessage());

            return failedResponse($e, false); // Return a 500 Internal Server Error status code
        }
    }

    /**
     * Validate incoming request
     *
     * @param Request $request
     * @return Object $validated
     */
    public function validateRequest(Request $request): object
    {
        return Validator::make($request->all(), [
            'limit' => 'integer|min:1|max:100'
        ]);
    }
}
