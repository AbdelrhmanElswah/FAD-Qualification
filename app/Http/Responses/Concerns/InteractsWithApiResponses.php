<?php

declare(strict_types=1);

namespace App\Http\Responses\Concerns;

trait InteractsWithApiResponses
{
    use SendsErrorResponses;
    use SendsSuccessResponses;
}
