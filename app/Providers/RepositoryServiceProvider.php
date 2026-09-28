<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Eloquent\TaskRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds every repository contract to its concrete implementation.
 *
 * Services depend on the interfaces only, so swapping in a different persistence
 * layer (or a fake in tests) is a one-line change here.
 */
final class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Contract => implementation.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        TaskRepositoryInterface::class => TaskRepository::class,
    ];
}
