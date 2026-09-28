<?php

declare(strict_types=1);

namespace App\Http\Responses\Concerns;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait SendsErrorResponses
{
    /**
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
     * @param  array<string, list<string>>  $errors
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'The given data was invalid.',
    ): JsonResponse {
        return $this->errorResponse($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    protected function notFoundResponse(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    protected function serverErrorResponse(string $message = 'Server error.'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
