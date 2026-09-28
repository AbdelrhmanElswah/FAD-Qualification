<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

trait NormalizesBooleanInput
{
    /**
     * @param  list<string>  $keys
     */
    protected function normalizeBooleans(array $keys): void
    {
        $normalized = [];

        foreach ($keys as $key) {
            if (! $this->has($key)) {
                continue;
            }

            $value = $this->input($key);

            if (! is_string($value)) {
                continue;
            }

            $boolean = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($boolean !== null) {
                $normalized[$key] = $boolean;
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }
}
