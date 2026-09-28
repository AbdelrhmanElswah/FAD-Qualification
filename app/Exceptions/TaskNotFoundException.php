<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

final class TaskNotFoundException extends ApiException
{
    public static function withId(int $id): self
    {
        return new self(
            message: "Task [{$id}] was not found.",
            status: Response::HTTP_NOT_FOUND,
        );
    }
}
