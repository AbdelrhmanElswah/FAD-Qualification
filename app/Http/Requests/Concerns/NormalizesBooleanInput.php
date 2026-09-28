<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

/**
 * Laravel's `boolean` rule rejects the strings "true"/"false", which is exactly
 * what clients send in query strings and HTML form bodies. This trait coerces
 * those into real booleans before validation runs.
 *
 * Values that are not recognisable booleans are left untouched so the `boolean`
 * rule still reports them as invalid.
 */
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
