<?php

declare(strict_types=1);

namespace App\Http\Responses\Concerns;

/**
 * Convenience trait that pulls in both halves of the API response contract.
 */
trait InteractsWithApiResponses
{
    use SendsErrorResponses;
    use SendsSuccessResponses;
}
