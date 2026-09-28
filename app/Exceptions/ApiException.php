<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base class for domain failures that map directly onto an HTTP response.
 *
 * Subclasses only declare *what* went wrong; ApiExceptionRenderer decides how it
 * is serialised, so no controller ever needs a try/catch block.
 */
abstract class ApiException extends RuntimeException
{
    /**
     * @param  array<string, list<string>>  $errors
     */
    public function __construct(
        string $message,
        private readonly int $status = Response::HTTP_BAD_REQUEST,
        private readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    /**
     * The HTTP status code this failure should be reported with.
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * Field-specific details, keyed by input name.
     *
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
