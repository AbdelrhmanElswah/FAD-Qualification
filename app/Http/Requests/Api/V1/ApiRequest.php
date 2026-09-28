<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Concerns\NormalizesBooleanInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared base for every API request object.
 *
 * Validation failures bubble up as a ValidationException and are converted into
 * the standard error envelope by ApiExceptionRenderer.
 */
abstract class ApiRequest extends FormRequest
{
    use NormalizesBooleanInput;

    /**
     * The endpoints are public, so authorisation always passes. Policies would
     * plug in here once authentication is introduced.
     */
    public function authorize(): bool
    {
        return true;
    }
}
