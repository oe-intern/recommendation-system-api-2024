<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HTTPResponseStatus;

trait ApiResponse
{
    /**
     * Success response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return Response
     */
    protected function successResponse(
        string $message,
        mixed $data = null,
        int $statusCode = HTTPResponseStatus::HTTP_OK,
    ): Response {
        return response()->success($message, $data, $statusCode);
    }

    /**
     * Error response.
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @return Response
     */
    protected function errorResponse(
        string $message,
        array $errors = [],
        int $statusCode = HTTPResponseStatus::HTTP_BAD_REQUEST,
    ): Response {
        return response()->error($message, $errors, $statusCode);
    }
}
