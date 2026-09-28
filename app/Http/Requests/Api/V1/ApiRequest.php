<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Concerns\NormalizesBooleanInput;
use Illuminate\Foundation\Http\FormRequest;

abstract class ApiRequest extends FormRequest
{
    use NormalizesBooleanInput;

    public function authorize(): bool
    {
        return true;
    }
}
