<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait ApiResponseTrait
{
    /**
     * Build success response with data
     */
    public function sendResponse(array|object $data, ?string $message = null, int $code = Response::HTTP_OK)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        return response()->json($response, $code);
    }

    /**
     * Build success response without data
     */
    public function sendSuccess(string $message, int $code = Response::HTTP_OK)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Build error response
     */
    public function sendError(string $message, array $errorMessages = [], int $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        // Include debug info only in non-production environments
        if (!app()->environment('production')) {
            $response['debug'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        }

        return response()->json($response, $code);
    }

    /**
     * Build success microservice response
     */
    public function sendServiceResponse(array|string $data, int $code = Response::HTTP_OK)
    {
        return response()->json(['success' => true, 'data' => $data], $code);
    }

    /**
     * Build error microservice response
     */
    public function sendServiceError(array|string $message, int $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }
}
