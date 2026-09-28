<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Responses\Concerns\SendsErrorResponses;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Translates every exception raised on an API route into the shared error envelope.
 *
 * Registered in bootstrap/app.php. Returning null hands the exception back to
 * Laravel's default handling, which is what we want for non-API requests.
 */
final class ApiExceptionRenderer
{
    use SendsErrorResponses;

    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*') && ! $request->expectsJson()) {
            return null;
        }

        return match (true) {
            $e instanceof ApiException => $this->errorResponse($e->getMessage(), $e->status(), $e->errors()),
            $e instanceof ValidationException => $this->validationErrorResponse($e->errors()),
            $e instanceof ModelNotFoundException => $this->notFoundResponse('The requested resource was not found.'),
            $e instanceof NotFoundHttpException => $this->notFoundResponse('The requested endpoint does not exist.'),
            $e instanceof MethodNotAllowedHttpException => $this->errorResponse(
                'This method is not allowed for the requested endpoint.',
                Response::HTTP_METHOD_NOT_ALLOWED,
            ),
            $e instanceof AuthenticationException => $this->errorResponse(
                'Unauthenticated.',
                Response::HTTP_UNAUTHORIZED,
            ),
            $e instanceof AuthorizationException => $this->errorResponse(
                'This action is unauthorized.',
                Response::HTTP_FORBIDDEN,
            ),
            $e instanceof HttpExceptionInterface => $this->errorResponse(
                $e->getMessage() !== '' ? $e->getMessage() : 'Request failed.',
                $e->getStatusCode(),
            ),
            default => $this->serverErrorResponse($this->unexpectedFailureMessage($e)),
        };
    }

    /**
     * Never leak internals in production, but stay debuggable locally.
     */
    private function unexpectedFailureMessage(Throwable $e): string
    {
        return config('app.debug') === true
            ? $e->getMessage()
            : 'An unexpected error occurred. Please try again later.';
    }
}
