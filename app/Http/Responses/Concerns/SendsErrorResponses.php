<?php

declare(strict_types=1);

namespace App\Http\Responses\Concerns;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds the single error envelope used by every endpoint:
 *
 *     { "success": false, "message": "...", "errors": {...} }
 *
 * The `errors` key is omitted when there is nothing field-specific to report.
 */
trait SendsErrorResponses
{
    /**
     * Return an error response.
     *
     * @param  array<string, list<string>>  $errors
     */
    protected function errorResponse(
        string $message = 'Something went wrong.',
        int $status = Response::HTTP_BAD_REQUEST,
        array $errors = [],
    ): JsonResponse {
        $payload = ['success' => false, 'message' => $message];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return new JsonResponse($payload, $status);
    }

    /**
     * Return a 422 response describing why validation failed.
     *
     * @param  array<string, list<string>>  $errors
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'The given data was invalid.',
    ): JsonResponse {
        return $this->errorResponse($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Return a 404 response.
     */
    protected function notFoundResponse(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Return a 500 response.
     */
    protected function serverErrorResponse(string $message = 'Server error.'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
