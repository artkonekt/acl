<?php

declare(strict_types=1);

/**
 * @param string $guard
 *
 * @return string|null
 */
function getModelForGuard(string $guard)
{
    return collect(config('auth.guards'))
        ->map(function ($guard) {
            return config("auth.providers.{$guard['provider']}.model");
        })->get($guard);
}
